<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Admin dashboard summary.
     */
    public function dashboard()
    {
        $articlesCount = Article::count();
        $membersCount = Member::count();
        $recentArticles = Article::latest()->take(5)->get();
        $recentMembers = Member::latest()->take(5)->get();

        return view('admin.dashboard', compact('articlesCount', 'membersCount', 'recentArticles', 'recentMembers'));
    }

    /* -------------------------------------------------------------------------- */
    /*                                ARTICLES CRUD                               */
    /* -------------------------------------------------------------------------- */

    public function articleIndex()
    {
        $articles = Article::latest()->paginate(15);
        return view('admin.articles.index', compact('articles'));
    }

    public function articleCreate()
    {
        return view('admin.articles.create');
    }

    public function articleStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'content' => 'required|string',
            'pdf' => 'nullable|file|mimes:pdf|max:102400',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|string|in:draft,published',
            'locale' => 'required|string|in:en,hi',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        // Handle unique slug
        $slugCount = Article::where('slug', 'like', $validated['slug'] . '%')->count();
        if ($slugCount > 0) {
            $validated['slug'] .= '-' . ($slugCount + 1);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('articles', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        if ($request->hasFile('pdf')) {
            $pdfFile = $request->file('pdf');
            $pdfPath = $pdfFile->store('articles/pdfs', 'public');
            $validated['pdf_path'] = '/storage/' . $pdfPath;
        }

        Article::create($validated);

        return redirect()->route('admin.articles.index')->with('success', 'Article created successfully.');
    }

    public function articleEdit($id)
    {
        $article = Article::findOrFail($id);
        return view('admin.articles.edit', compact('article'));
    }

    public function articleUpdate(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'content' => 'required|string',
            'pdf' => 'nullable|file|mimes:pdf|max:102400',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|string|in:draft,published',
            'locale' => 'required|string|in:en,hi',
        ]);

        if ($validated['title'] !== $article->title) {
            $validated['slug'] = Str::slug($validated['title']);
            $slugCount = Article::where('slug', 'like', $validated['slug'] . '%')->where('id', '!=', $id)->count();
            if ($slugCount > 0) {
                $validated['slug'] .= '-' . ($slugCount + 1);
            }
        }

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($article->image_path) {
                $oldPath = str_replace('/storage/', '', $article->image_path);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('articles', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        $removePdf = $request->boolean('remove_pdf');

        if ($request->hasFile('pdf')) {
            // Delete old PDF if exists
            if ($article->pdf_path) {
                $oldPdfPath = str_replace('/storage/', '', $article->pdf_path);
                Storage::disk('public')->delete($oldPdfPath);
            }

            $pdfFile = $request->file('pdf');
            $pdfPath = $pdfFile->store('articles/pdfs', 'public');
            $validated['pdf_path'] = '/storage/' . $pdfPath;
        } elseif ($removePdf) {
            if ($article->pdf_path) {
                $oldPdfPath = str_replace('/storage/', '', $article->pdf_path);
                Storage::disk('public')->delete($oldPdfPath);
            }
            $validated['pdf_path'] = null;
        }

        $article->update($validated);

        return redirect()->route('admin.articles.index')->with('success', 'Article updated successfully.');
    }

    public function articleDelete($id)
    {
        $article = Article::findOrFail($id);
        if ($article->image_path) {
            $oldPath = str_replace('/storage/', '', $article->image_path);
            Storage::disk('public')->delete($oldPath);
        }
        if ($article->pdf_path) {
            $oldPdfPath = str_replace('/storage/', '', $article->pdf_path);
            Storage::disk('public')->delete($oldPdfPath);
        }
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Article deleted successfully.');
    }

    /* -------------------------------------------------------------------------- */
    /*                                MEMBERS CRUD                                */
    /* -------------------------------------------------------------------------- */

    public function memberIndex(Request $request)
    {
        $query = Member::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $members = $query->latest()->paginate(20);
        $categories = $this->getCategoryMap();

        return view('admin.members.index', compact('members', 'categories'));
    }

    public function memberCreate()
    {
        $categories = $this->getCategoryMap();
        return view('admin.members.create', compact('categories'));
    }

    public function memberStore(Request $request)
    {
        $isGallery = in_array($request->category, ['photos-gallery', 'advertisements-gallery']);

        $validated = $request->validate([
            'name' => $isGallery ? 'nullable|string|max:255' : 'required|string|max:255',
            'designation' => $isGallery ? 'nullable|string|max:255' : 'required|string|max:255',
            'category' => 'required|string',
            'state' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'video' => 'nullable|file|mimes:mp4,ogv,webm,avi,mov|max:102400',
            'video_url' => 'nullable|url|max:255',
            'pdf' => 'nullable|file|mimes:pdf|max:102400',
            'contact_info' => 'nullable|string',
            'locale' => 'required|string|in:en,hi',
        ]);

        if ($isGallery) {
            $validated['name'] = $validated['name'] ?? 'Gallery Item';
            $validated['designation'] = $validated['designation'] ?? '';
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('members', 'public');
            $validated['photo_path'] = '/storage/' . $path;
        }

        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('members/videos', 'public');
            $validated['video_url'] = '/storage/' . $path;
        } elseif (!empty($validated['video_url'])) {
            // Keep the entered video url string as validated video_url
        }

        if ($request->hasFile('pdf')) {
            $pdfFile = $request->file('pdf');
            $pdfPath = $pdfFile->store('members/pdfs', 'public');
            $validated['pdf_path'] = '/storage/' . $pdfPath;
        }

        Member::create($validated);

        return redirect()->route('admin.members.index')->with('success', 'Member added successfully.');
    }

    public function memberEdit($id)
    {
        $member = Member::findOrFail($id);
        $categories = $this->getCategoryMap();
        return view('admin.members.edit', compact('member', 'categories'));
    }

    public function memberUpdate(Request $request, $id)
    {
        $member = Member::findOrFail($id);
        $isGallery = in_array($request->category, ['photos-gallery', 'advertisements-gallery']);

        $validated = $request->validate([
            'name' => $isGallery ? 'nullable|string|max:255' : 'required|string|max:255',
            'designation' => $isGallery ? 'nullable|string|max:255' : 'required|string|max:255',
            'category' => 'required|string',
            'state' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'video' => 'nullable|file|mimes:mp4,ogv,webm,avi,mov|max:102400',
            'video_url' => 'nullable|url|max:255',
            'pdf' => 'nullable|file|mimes:pdf|max:102400',
            'contact_info' => 'nullable|string',
            'locale' => 'required|string|in:en,hi',
        ]);

        if ($isGallery) {
            $validated['name'] = $validated['name'] ?? 'Gallery Item';
            $validated['designation'] = $validated['designation'] ?? '';
        }

        if ($request->hasFile('photo')) {
            if ($member->photo_path) {
                $oldPath = str_replace('/storage/', '', $member->photo_path);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('photo')->store('members', 'public');
            $validated['photo_path'] = '/storage/' . $path;
        }

        $removeVideo = $request->boolean('remove_video');

        if ($request->hasFile('video')) {
            if ($member->video_url && str_starts_with($member->video_url, '/storage/')) {
                $oldVideoPath = str_replace('/storage/', '', $member->video_url);
                Storage::disk('public')->delete($oldVideoPath);
            }
            $path = $request->file('video')->store('members/videos', 'public');
            $validated['video_url'] = '/storage/' . $path;
        } elseif ($removeVideo) {
            if ($member->video_url && str_starts_with($member->video_url, '/storage/')) {
                $oldVideoPath = str_replace('/storage/', '', $member->video_url);
                Storage::disk('public')->delete($oldVideoPath);
            }
            $validated['video_url'] = null;
        } elseif (!empty($validated['video_url'])) {
            // Keep the new url string, but if there was a local video file, delete it
            if ($member->video_url && str_starts_with($member->video_url, '/storage/')) {
                $oldVideoPath = str_replace('/storage/', '', $member->video_url);
                Storage::disk('public')->delete($oldVideoPath);
            }
        }

        $removePdf = $request->boolean('remove_pdf');

        if ($request->hasFile('pdf')) {
            if ($member->pdf_path) {
                $oldPdfPath = str_replace('/storage/', '', $member->pdf_path);
                Storage::disk('public')->delete($oldPdfPath);
            }

            $pdfFile = $request->file('pdf');
            $pdfPath = $pdfFile->store('members/pdfs', 'public');
            $validated['pdf_path'] = '/storage/' . $pdfPath;
        } elseif ($removePdf) {
            if ($member->pdf_path) {
                $oldPdfPath = str_replace('/storage/', '', $member->pdf_path);
                Storage::disk('public')->delete($oldPdfPath);
            }
            $validated['pdf_path'] = null;
        }

        $member->update($validated);

        return redirect()->route('admin.members.index')->with('success', 'Member updated successfully.');
    }

    public function memberDelete($id)
    {
        $member = Member::findOrFail($id);
        if ($member->photo_path) {
            $oldPath = str_replace('/storage/', '', $member->photo_path);
            Storage::disk('public')->delete($oldPath);
        }
        if ($member->video_url && str_starts_with($member->video_url, '/storage/')) {
            $oldVideoPath = str_replace('/storage/', '', $member->video_url);
            Storage::disk('public')->delete($oldVideoPath);
        }
        if ($member->pdf_path) {
            $oldPdfPath = str_replace('/storage/', '', $member->pdf_path);
            Storage::disk('public')->delete($oldPdfPath);
        }
        $member->delete();

        return redirect()->route('admin.members.index')->with('success', 'Member deleted successfully.');
    }

    /**
     * Map of all categories.
     */
    private function getCategoryMap(): array
    {
        return [
            'national-parliamentary-board' => 'National Parliamentary Board - राष्ट्रीय संसदीय बोर्ड',
            'publishers' => "Publisher's Details - प्रकाशक विवरण",
            'prime-editor' => 'Prime Editor - प्रधान संपादक',
            'printers' => "Vigyanmev Jayate Printer's - मुद्रक विवरण",
            'honours' => 'Vigyanmev Jayate Honours - वैज्ञानिक सम्मान',
            'authorized-persons' => "Authorized Person's - अधिकृत व्यक्ति",
            'advocates' => 'Advocates & Legal Advisors - अधिवक्ता और कानूनी सलाहकार',
            'state-news-editors' => 'State News Editors - राज्य समाचार संपादक',
            'state-press-club-presidents' => 'State Press Club Presidents - राज्य प्रेस क्लब अध्यक्ष',
            'commissionery-presidents' => 'Commissionery Presidents - आयुक्त मंडल अध्यक्ष',
            'women-press-club' => "Women's Press Club Presidents & Secretaries - महिला प्रेस क्लब अध्यक्ष और सचिव",
            'engineers' => 'Our Digital AI Computers & Software Engineers - डिजिटल एआई कंप्यूटर और सॉफ्टवेयर इंजीनियर्स',
            'district-press-club' => 'District Press Club Presidents - जिला प्रेस क्लब अध्यक्ष',
            'district-news-bureau' => 'District News Bureau Secretaries - जिला समाचार ब्यूरो सचिव',
            'subdivision-press-club' => 'Subdivision Press Club Presidents & Bureau - अनुमंडल प्रेस क्लब अध्यक्ष और ब्यूरो',
            'block-press-club' => 'Block Press Club Presidents, Secretaries & Bureau - ब्लॉक प्रेस क्लब अध्यक्ष, सचिव और ब्यूरो',
            'panchayat-press-club' => 'Panchayat Press Club Presidents & Secretaries - पंचायत प्रेस क्लब अध्यक्ष और सचिव',
            'news-bureau' => 'News Bureau Details - समाचार ब्यूरो विवरण',
            'translators' => 'Language Translators - भाषा अनुवादक',
            'documentary-films' => 'Our Documentary Films, Actors & Actresses - हमारे वृत्तचित्र फिल्म, अभिनेता और अभिनेत्री',
            'youtubers' => 'Youtubs Press Club Details - यूट्यूबर्स प्रेस क्लब विवरण',
            'media-training' => 'Print and Electronics Media Training Centres - प्रिंट और इलेक्ट्रॉनिक्स मीडिया प्रशिक्षण केंद्र',
            'social-media-training' => 'Digital Social Media Training Centres - डिजिटल सोशल मीडिया प्रशिक्षण केंद्र',
            'schools-colleges' => 'Our Schools & Colleges - हमारे स्कूल और कॉलेज',
            'offices' => "Our Press Club Office's - हमारे प्रेस क्लब कार्यालय",
            'subscribers' => 'Magazine Subscribers & Life Times - पत्रिका ग्राहक और लाइफ टाइम',
            'life-members' => "Life Members & Donors' Details - आजीवन सदस्य और दानदाता विवरण",
            'e-papers-magazines' => "E-Papers & Magazines - ई-पेपर और पत्रिकाएं",
            'photos-gallery' => 'Photo Gallery - फोटो गैलरी',
            'advertisements-gallery' => 'Advertisement Gallery - विज्ञापन गैलरी',
        ];
    }
}
