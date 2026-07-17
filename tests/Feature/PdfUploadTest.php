<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_pdf_and_link_it(): void
    {
        // 1. Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        Storage::fake('public');

        $pdfPath = base_path('sample.pdf');
        $uploadedFile = new UploadedFile($pdfPath, 'sample.pdf', 'application/pdf', null, true);

        // 2. Act as admin and POST create article
        $response = $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Test PDF Dynamic Article',
                'category' => 'epaper',
                'content' => 'My article body text',
                'pdf' => $uploadedFile,
                'status' => 'published',
                'locale' => 'en',
            ]);

        $response->assertRedirect(route('admin.articles.index'));

        // 3. Assert article is created and PDF path is saved
        $article = Article::latest()->first();
        $this->assertNotNull($article);
        $this->assertEquals('Test PDF Dynamic Article', $article->title);
        $this->assertEquals('My article body text', $article->content);
        $this->assertNotNull($article->pdf_path);

        // 4. Assert PDF file is stored on disk
        $diskPath = str_replace('/storage/', '', $article->pdf_path);
        Storage::disk('public')->assertExists($diskPath);

        // 5. Assert page detail renders the PDF link button correctly
        $frontendResponse = $this->get(route('news.detail', $article->slug));
        $frontendResponse->assertStatus(200);
        $frontendResponse->assertSee(route('pdf.viewer', ['file' => $article->pdf_path]));
    }

    public function test_admin_can_remove_pdf_from_article(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        Storage::fake('public');

        $pdfPath = base_path('sample.pdf');
        $uploadedFile = new UploadedFile($pdfPath, 'sample.pdf', 'application/pdf', null, true);

        // Create article
        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Article to Edit',
                'category' => 'epaper',
                'content' => 'Article body content',
                'pdf' => $uploadedFile,
                'status' => 'published',
                'locale' => 'en',
            ]);

        $article = Article::latest()->first();
        $this->assertNotNull($article->pdf_path);

        // Update article with remove_pdf check
        $response = $this->actingAs($admin)
            ->post(route('admin.articles.update', $article->id), [
                'title' => 'Article to Edit Updated',
                'category' => 'epaper',
                'content' => 'Fallback typed content here',
                'remove_pdf' => '1',
                'status' => 'published',
                'locale' => 'en',
            ]);

        $response->assertRedirect(route('admin.articles.index'));

        $article->refresh();
        $this->assertNull($article->pdf_path);
        $this->assertEquals('Fallback typed content here', $article->content);
    }

    public function test_admin_can_upload_pdf_for_member_and_link_it(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        Storage::fake('public');

        $pdfPath = base_path('sample.pdf');
        $uploadedFile = new UploadedFile($pdfPath, 'sample.pdf', 'application/pdf', null, true);

        // Act as admin and POST create member
        $response = $this->actingAs($admin)
            ->post(route('admin.members.store'), [
                'name' => 'Test Member',
                'designation' => 'Secretary',
                'category' => 'subscribers',
                'contact_info' => 'My member contact info',
                'pdf' => $uploadedFile,
                'locale' => 'en',
            ]);

        $response->assertRedirect(route('admin.members.index'));

        // Assert member is created and PDF path is saved
        $member = \App\Models\Member::latest()->first();
        $this->assertNotNull($member);
        $this->assertEquals('Test Member', $member->name);
        $this->assertEquals('My member contact info', $member->contact_info);
        $this->assertNotNull($member->pdf_path);

        // Assert PDF file is stored on disk
        $diskPath = str_replace('/storage/', '', $member->pdf_path);
        Storage::disk('public')->assertExists($diskPath);

        // Assert directory listing renders the PDF link correctly
        $frontendResponse = $this->get(route('directory.show', 'subscribers'));
        $frontendResponse->assertStatus(200);
        $frontendResponse->assertSee(route('pdf.viewer', ['file' => $member->pdf_path]));
    }

    public function test_admin_can_remove_pdf_from_member(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        Storage::fake('public');

        $pdfPath = base_path('sample.pdf');
        $uploadedFile = new UploadedFile($pdfPath, 'sample.pdf', 'application/pdf', null, true);

        // Create member
        $this->actingAs($admin)
            ->post(route('admin.members.store'), [
                'name' => 'Member to Edit',
                'designation' => 'President',
                'category' => 'subscribers',
                'contact_info' => 'Member contact details',
                'pdf' => $uploadedFile,
                'locale' => 'en',
            ]);

        $member = \App\Models\Member::latest()->first();
        $this->assertNotNull($member->pdf_path);

        // Update member with remove_pdf check
        $response = $this->actingAs($admin)
            ->post(route('admin.members.update', $member->id), [
                'name' => 'Member to Edit Updated',
                'designation' => 'President',
                'category' => 'subscribers',
                'contact_info' => 'Fallback contact details',
                'remove_pdf' => '1',
                'locale' => 'en',
            ]);

        $response->assertRedirect(route('admin.members.index'));

        $member->refresh();
        $this->assertNull($member->pdf_path);
        $this->assertEquals('Fallback contact details', $member->contact_info);
    }

    public function test_admin_can_upload_pdf_for_navbar_menu(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        Storage::fake('public');

        $pdfPath = base_path('sample.pdf');
        $uploadedFile = new UploadedFile($pdfPath, 'sample.pdf', 'application/pdf', null, true);

        // 1. Create navbar menu item of type 'pdf'
        $response = $this->actingAs($admin)
            ->post(route('admin.navigation.store'), [
                'title_en' => 'My PDF Link',
                'title_hi' => 'मेरा पीडीएफ लिंक',
                'type' => 'pdf',
                'sort_order' => 1,
                'status' => 'published',
                'pdf' => $uploadedFile,
            ]);

        $response->assertRedirect(route('admin.navigation.index'));

        $menu = \App\Models\NavigationMenu::where('title_en', 'My PDF Link')->first();
        $this->assertNotNull($menu);
        $this->assertEquals('My PDF Link', $menu->title_en);
        $this->assertNotNull($menu->pdf_path);

        // Verify PDF file exists
        $diskPath = str_replace('/storage/', '', $menu->pdf_path);
        Storage::disk('public')->assertExists($diskPath);

        $uploadedFile2 = new UploadedFile($pdfPath, 'sample.pdf', 'application/pdf', null, true);
        $responsePage = $this->actingAs($admin)
            ->post(route('admin.navigation.store'), [
                'title_en' => 'My Page Link',
                'title_hi' => 'मेरा पेज लिंक',
                'type' => 'page',
                'sort_order' => 2,
                'status' => 'published',
                'content_en' => 'English page content',
                'content_hi' => 'Hindi page content',
                'layout_type' => 'standard',
                'pdf' => $uploadedFile2,
            ]);

        $responsePage->assertRedirect(route('admin.navigation.index'));

        $page = \App\Models\NavigationMenu::where('title_en', 'My Page Link')->first();
        $this->assertNotNull($page);
        $this->assertNotNull($page->pdf_path);

        // Verify dynamic page displays the View PDF button
        $frontendResponse = $this->get(route('pages.show', $page->slug));
        $frontendResponse->assertStatus(200);
        $frontendResponse->assertSee(route('pdf.viewer', ['file' => $page->pdf_path]));
    }

    public function test_guest_can_view_embedded_pdf_viewer(): void
    {
        $response = $this->get(route('pdf.viewer', ['file' => '/storage/navigation/pdfs/sample.pdf']));
        
        $response->assertStatus(200);
        $response->assertSee('Document Viewer');
        $response->assertSee('<iframe src="/storage/navigation/pdfs/sample.pdf#toolbar=0"', false);
    }

    public function test_admin_can_upload_video_for_gallery(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        Storage::fake('public');

        $videoFile = UploadedFile::fake()->create('sample.mp4', 500, 'video/mp4');

        // Store video gallery item
        $response = $this->actingAs($admin)
            ->post(route('admin.members.store'), [
                'name' => 'Test Video Title',
                'designation' => 'Test Video Description',
                'category' => 'photos-gallery',
                'video' => $videoFile,
                'locale' => 'en',
            ]);

        $response->assertRedirect(route('admin.members.index'));

        $member = \App\Models\Member::latest()->first();
        $this->assertNotNull($member);
        $this->assertEquals('Test Video Title', $member->name);
        $this->assertNotNull($member->video_url);

        $diskPath = str_replace('/storage/', '', $member->video_url);
        Storage::disk('public')->assertExists($diskPath);

        // Fetch index page as admin and verify video is listed in the grid view
        $indexResponse = $this->actingAs($admin)->get(route('admin.members.index', ['category' => 'photos-gallery']));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Test Video Title');
        $indexResponse->assertSee('video src="' . $member->video_url . '"', false);
    }
}
