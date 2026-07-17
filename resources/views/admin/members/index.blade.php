@extends('layouts.admin')

@section('header_title', 'Manage Directories & Press Clubs')

@section('admin_content')

    <!-- Category Filter Selector -->
    <div class="admin-card">
        <form action="{{ route('admin.members.index') }}" method="GET" style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex-grow: 1; min-width: 280px;">
                <label for="category" style="display: block; font-weight: bold; font-size: 0.85rem; margin-bottom: 8px; color: var(--primary-color);">Filter by Board/Press Club Category:</label>
                <select name="category" id="category" class="form-control" onchange="this.form.submit()">
                    <option value="">-- All Categories (Showing All Members) --</option>
                    @foreach($categories as $key => $value)
                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('admin.members.index') }}" class="btn-action" style="background-color: #cbd5e1; color: #334155; padding: 12px 18px; border-radius: 6px; font-weight: bold; border: 1px solid #cbd5e1;">Reset Filter</a>
                <a href="{{ route('admin.members.create') }}" class="btn-primary" style="padding: 12px 20px;">➕ Add Member</a>
            </div>
        </form>
    </div>

    <!-- Listings Table / Gallery Grid -->
    <div class="admin-card">
        <h3 style="margin-bottom: 15px; color: var(--primary-color);">Directory Listings</h3>

        @php
            $isGalleryFilter = in_array(request('category'), ['photos-gallery', 'advertisements-gallery']);
        @endphp

        @if($isGalleryFilter)
            <!-- Gallery Grid View -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; margin-top: 15px; margin-bottom: 25px;">
                @forelse($members as $member)
                    <div style="background: white; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; box-shadow: var(--shadow-sm); position: relative; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='var(--shadow-md)';" onmouseout="this.style.transform='none'; this.style.boxShadow='var(--shadow-sm)';">
                        <!-- Media Preview Container -->
                        <div style="height: 180px; width: 100%; overflow: hidden; background-color: #f1f5f9; position: relative; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center;">
                            @if($member->video_url)
                                @if(str_starts_with($member->video_url, '/storage/'))
                                    <!-- Local video player thumbnail -->
                                    <video src="{{ $member->video_url }}" style="width: 100%; height: 100%; object-fit: cover;"></video>
                                @else
                                    <!-- External video icon/link -->
                                    @php
                                        // Simple youtube thumbnail fetch
                                        $ytId = '';
                                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $member->video_url, $match)) {
                                            $ytId = $match[1];
                                        }
                                    @endphp
                                    @if($ytId)
                                        <img src="https://img.youtube.com/vi/{{ $ytId }}/0.jpg" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <div style="font-size: 3.5rem; color: #cbd5e1;">🎥</div>
                                    @endif
                                @endif
                                <div style="position: absolute; top: 10px; left: 10px; background: rgba(15, 23, 42, 0.75); color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; display: flex; align-items: center; gap: 4px;">
                                    <span>🎥</span> Video
                                </div>
                            @elseif($member->photo_path)
                                <img src="{{ $member->photo_path }}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                <div style="position: absolute; top: 10px; left: 10px; background: rgba(15, 23, 42, 0.75); color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; display: flex; align-items: center; gap: 4px;">
                                    <span>🖼️</span> Photo
                                </div>
                            @else
                                <div style="font-size: 3.5rem; color: #94a3b8;">🖼️</div>
                            @endif

                            <div style="position: absolute; top: 10px; right: 10px; background: var(--primary-color); color: white; padding: 3px 6px; border-radius: 3px; font-size: 0.65rem; font-weight: bold; text-transform: uppercase;">
                                {{ $member->locale }}
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div style="padding: 15px; display: flex; flex-direction: column; flex-grow: 1;">
                            <h4 style="margin: 0 0 6px; font-size: 0.95rem; font-weight: 700; color: var(--primary-color); line-height: 1.3;">
                                {{ $member->name }}
                            </h4>
                            @if($member->designation)
                                <p style="margin: 0; font-size: 0.78rem; color: var(--text-muted); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;">
                                    {{ $member->designation }}
                                </p>
                            @else
                                <p style="margin: 0; font-size: 0.75rem; color: #94a3b8; font-style: italic;">No caption/description</p>
                            @endif

                            <!-- Actions -->
                            <div style="margin-top: auto; padding-top: 15px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                                <a href="{{ route('admin.members.edit', $member->id) }}" class="btn-action btn-edit" style="font-size: 0.75rem; padding: 6px 12px; height: auto; display: inline-flex; align-items: center;">Edit</a>
                                <form action="{{ route('admin.members.delete', $member->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this gallery item?');" style="margin: 0; display: inline-block;">
                                    @csrf
                                    <button type="submit" class="btn-action btn-delete" style="font-size: 0.75rem; padding: 6px 12px; height: auto; border: none; cursor: pointer;">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 50px; background: white; border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-muted); font-weight: 500;">
                        No gallery items found. Click "Add Member" to upload photos/videos.
                    </div>
                @endforelse
            </div>
        @else
            <!-- Listings Table -->
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Category</th>
                            <th>Language</th>
                            <th>Location</th>
                            <th>Contact Info</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $index => $member)
                            <tr>
                                <td style="font-weight: bold; color: var(--text-muted);">{{ $members->firstItem() + $index }}</td>
                                <td>
                                    @if($member->photo_path)
                                        <img src="{{ $member->photo_path }}" alt="" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border-color);">
                                    @elseif($member->video_url)
                                        <div style="width: 35px; height: 35px; border-radius: 50%; background-color: var(--accent-gold); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;" title="Video item">🎥</div>
                                    @else
                                        <div style="width: 35px; height: 35px; border-radius: 50%; background-color: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;">
                                            {{ mb_strtoupper(mb_substr($member->name, 0, 1, 'UTF-8'), 'UTF-8') }}
                                        </div>
                                    @endif
                                </td>
                                <td style="font-weight: 700; color: var(--primary-color);">{{ $member->name }}</td>
                                <td><span class="member-badge">{{ $member->designation }}</span></td>
                                <td>
                                    <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">
                                        {{ str_replace('-', ' ', $member->category) }}
                                    </span>
                                </td>
                                <td><span class="badge-locale">{{ $member->locale }}</span></td>
                                <td style="font-size: 0.8rem;">
                                    {{ $member->district ? $member->district . ', ' : '' }}{{ $member->state ?? 'N/A' }}
                                </td>
                                <td style="font-size: 0.78rem; max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $member->contact_info }}">
                                    {{ $member->contact_info }}
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 8px;">
                                        <a href="{{ route('admin.members.edit', $member->id) }}" class="btn-action btn-edit">Edit</a>
                                        <form action="{{ route('admin.members.delete', $member->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this member profile?');">
                                            @csrf
                                            <button type="submit" class="btn-action btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 30px; color: var(--text-muted); font-weight: 500;">
                                    No registered directory members found. Click "Add Member" to register a new profile.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        <div>
            {{ $members->appends(request()->input())->links('partials.pagination') }}
        </div>
    </div>

@endsection
