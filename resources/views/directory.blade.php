@extends('layouts.app')

@section('title', __($title))

@section('content')

    <div class="page-banner">
        <div class="container">
            <h1>{{ strtoupper(__('Vigyanmev Jayate')) }} {{ strtoupper(__('Directories')) }}</h1>
            <div class="breadcrumb">
                <a href="{{ route('home') }}">{{ __('Home') }}</a> &raquo; 
                <span style="color: var(--accent-gold);">{{ __($title) }}</span>
            </div>
        </div>
    </div>

    <section class="directory-section" style="padding: 50px 0; background-color: #f8fafc;">
        <div class="container">
            
            <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <h2 style="font-size: 1.5rem; border-left: 4px solid var(--accent-color); padding-left: 10px; color: var(--primary-color);">{{ __($title) }}</h2>
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                    {{ __('Total Listings') }}: <span style="background-color: var(--primary-color); color: white; padding: 2px 8px; border-radius: 10px; font-weight: bold;">{{ count($members) }}</span>
                </div>
            </div>

            @if($category === 'photos-gallery' || $category === 'advertisements-gallery')
                <!-- Premium Media Gallery Grid -->
                @php
                    if (!function_exists('getYoutubeId')) {
                        function getYoutubeId($url) {
                            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $url, $match)) {
                                return $match[1];
                            }
                            return null;
                        }
                    }
                @endphp
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
                    @forelse($members as $member)
                        <div class="gallery-card" 
                             style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; display: flex; flex-direction: column; border: 1px solid var(--border-color);"
                             onclick="openGalleryLightbox({{ json_encode([
                                 'type' => $member->video_url ? 'video' : 'photo',
                                 'title' => $member->name,
                                 'description' => $member->designation,
                                 'media_url' => $member->video_url ?: $member->photo_path,
                                 'photo_url' => $member->photo_path ?: ''
                             ], JSON_UNESCAPED_SLASHES) }})">
                            
                            <div class="gallery-media-wrapper" style="height: 220px; overflow: hidden; position: relative; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                                @if($member->video_url)
                                    @php
                                        $ytId = getYoutubeId($member->video_url);
                                    @endphp
                                    @if($ytId)
                                        <img src="https://img.youtube.com/vi/{{ $ytId }}/hqdefault.jpg" alt="{{ $member->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;">
                                    @elseif($member->photo_path)
                                        <img src="{{ $member->photo_path }}" alt="{{ $member->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;">
                                    @else
                                        <div style="font-size: 3.5rem;">🎥</div>
                                    @endif
                                    <!-- Play Button Overlay -->
                                    <div class="video-play-overlay" style="position: absolute; inset: 0; background: rgba(15, 23, 42, 0.4); display: flex; align-items: center; justify-content: center; transition: background 0.3s ease;">
                                        <div class="play-button-circle" style="width: 60px; height: 60px; border-radius: 50%; background: var(--accent-gold); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; box-shadow: 0 4px 20px rgba(212, 175, 55, 0.4); transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                                            ▶
                                        </div>
                                    </div>
                                @elseif($member->photo_path)
                                    <img src="{{ $member->photo_path }}" alt="{{ $member->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;">
                                    <div class="photo-zoom-overlay" style="position: absolute; inset: 0; background: rgba(15, 23, 42, 0); display: flex; align-items: center; justify-content: center; transition: background 0.3s ease;">
                                        <span class="zoom-icon" style="font-size: 1.5rem; color: white; opacity: 0; transform: scale(0.8); transition: all 0.3s ease;">🔍</span>
                                    </div>
                                @else
                                    <div style="font-size: 3rem; color: #94a3b8;">🖼️</div>
                                @endif
                            </div>

                            <div class="gallery-card-info" style="padding: 15px; display: flex; flex-direction: column; flex-grow: 1;">
                                <h3 style="font-size: 1.05rem; margin: 0 0 5px; color: var(--primary-color); font-weight: 700; line-height: 1.4;">
                                    {{ $member->name }}
                                </h3>
                                @if($member->designation)
                                    <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;">
                                        {{ $member->designation }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="grid-column: 1 / -1; text-align: center; padding: 50px; background-color: white; border-radius: 8px; border: 1px solid var(--border-color); color: var(--text-muted); font-weight: 500;">
                            📭 {{ __('No media items uploaded in this gallery yet. You can add images from the Admin Panel.') }}
                        </div>
                    @endforelse
                </div>
            @else
                <!-- Filter Dropdown (Active for directories containing district names) -->
                @php
                    $districts = $members->pluck('district')->filter()->unique()->sort();
                @endphp

                @if($districts->count() > 0)
                    <div style="background: white; border: 1px solid #cbd5e1; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
                        <label for="districtFilter" style="font-weight: 700; color: #0f172a; font-size: 0.9rem;">
                            🔍 {{ __('Filter by District / जिला से फ़िल्टर करें') }}:
                        </label>
                        <select id="districtFilter" class="form-control" style="max-width: 300px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-weight: 600;" onchange="filterMembersByDistrict(this.value)">
                            <option value="">-- {{ __('All Districts / सभी जिले') }} --</option>
                            @foreach($districts as $dist)
                                <option value="{{ $dist }}">{{ $dist }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Table View for Directories -->
                <div class="data-table-wrapper" style="box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th style="width: 90px; text-align: center;">{{ __('Photo / फोटो') }}</th>
                                <th>{{ __('Name / नाम') }}</th>
                                <th>{{ __('Designation / पद') }}</th>
                                <th>{{ __('State / राज्य') }}</th>
                                <th>{{ __('District / जिला') }}</th>
                                <th style="text-align: center; width: 180px;">{{ __('Details / विवरण') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($members as $index => $member)
                                <tr class="member-row" data-district="{{ $member->district ?? '' }}">
                                    <td class="row-index" style="font-weight: bold; color: var(--text-muted);">{{ $index + 1 }}</td>
                                    <td style="text-align: center;">
                                        @if($member->photo_path)
                                            <img src="{{ $member->photo_path }}" alt="{{ $member->name }}" style="width: 50px; height: 50px; border-radius: 6px; object-fit: cover; border: 1px solid var(--border-color); background-color: #f1f5f9;">
                                        @else
                                            <div style="width: 50px; height: 50px; border-radius: 6px; background-color: #e2e8f0; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; border: 1px solid var(--border-color); margin: 0 auto;">👤</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span style="font-weight: 700; color: var(--primary-color);">{{ $member->name }}</span>
                                    </td>
                                    <td>
                                        <span class="member-badge">{{ $member->designation }}</span>
                                    </td>
                                    <td>{{ $member->state ?? 'N/A' }}</td>
                                    <td>{{ $member->district ?? 'N/A' }}</td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn-primary" 
                                            style="padding: 8px 14px; font-size: 0.75rem; border: none; cursor: pointer; border-radius: 4px; display: inline-flex; align-items: center; gap: 5px; font-weight: bold; background-color: var(--primary-color); color: white; transition: background-color 0.2s ease;"
                                            onclick="openMemberDetails({{ json_encode([
                                                'name' => $member->name,
                                                'designation' => $member->designation,
                                                'state' => $member->state ?? 'N/A',
                                                'district' => $member->district ?? 'N/A',
                                                'photo' => $member->photo_path ?? '',
                                                'contact' => $member->contact_info ?? '',
                                                'pdf_url' => $member->pdf_path ? route('pdf.viewer', ['file' => $member->pdf_path]) : ''
                                            ], JSON_UNESCAPED_SLASHES) }})">
                                            🔍 {{ __('View Details / विवरण देखें') }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted); font-weight: 500;">
                                        📭 {{ __('No registered members found in this category. You can add profiles from the Admin Panel.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </section>

    <!-- Profile Details Modal Popup -->
    <div id="memberDetailsModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s ease-in-out;">
        <div class="modal-container" style="background: white; width: 90%; max-width: 550px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15), 0 10px 10px -5px rgba(0,0,0,0.04); overflow: hidden; transform: scale(0.95); transition: transform 0.2s ease-in-out; border-top: 5px solid var(--accent-gold);">
            <!-- Modal Header -->
            <div style="padding: 20px 25px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #0f172a;">{{ __('Profile Details / विवरण') }}</h3>
                <button onclick="closeMemberDetails()" style="background: none; border: none; font-size: 1.7rem; color: #94a3b8; cursor: pointer; padding: 0; line-height: 1; transition: color 0.15s ease;">&times;</button>
            </div>
            <!-- Modal Body -->
            <div style="padding: 25px; display: flex; flex-direction: column; gap: 20px;">
                <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
                    <img id="modalMemberPhoto" src="" alt="Photo" style="width: 110px; height: 135px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1; background-color: #f1f5f9;">
                    <div style="display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 200px;">
                        <h4 id="modalMemberName" style="margin: 0; font-size: 1.25rem; font-weight: 800; color: var(--primary-color);"></h4>
                        <span id="modalMemberDesignation" class="member-badge" style="align-self: flex-start; background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 0.78rem;"></span>
                        <p style="margin: 8px 0 0; font-size: 0.85rem; color: #475569; line-height: 1.5;">
                            📍 <strong>{{ __('State / राज्य') }}:</strong> <span id="modalMemberState"></span><br>
                            🏢 <strong>{{ __('District / जिला') }}:</strong> <span id="modalMemberDistrict"></span>
                        </p>
                    </div>
                </div>
                
                <!-- Contact & Bio -->
                <div id="modalMemberContactContainer" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px 20px; display: none;">
                    <h5 style="margin: 0 0 10px; font-size: 0.88rem; font-weight: 700; color: #0f172a; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; display: flex; align-items: center; gap: 6px;">
                        <span>📞</span> {{ __('Contact & Address Details / संपर्क एवं पता') }}
                    </h5>
                    <p id="modalMemberContact" style="margin: 0; font-size: 0.82rem; color: #334155; line-height: 1.6; white-space: pre-line; font-family: monospace;"></p>
                </div>

                <!-- PDF File Viewer Button -->
                <div id="modalMemberPdfContainer" style="display: none; margin-top: 5px;">
                    <a id="modalMemberPdfUrl" href="#" class="btn-primary" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; padding: 12px 24px; font-weight: bold; background-color: #2563eb; border-radius: 4px; color: white; width: 100%; justify-content: center; box-shadow: 0 4px 6px rgba(37, 99, 235, 0.15); transition: background-color 0.2s ease;">
                        📄 {{ __('View Attached PDF Document / दस्तावेज देखें') }}
                    </a>
                </div>
            </div>
            <!-- Modal Footer -->
            <div style="background-color: #f8fafc; padding: 15px 25px; border-top: 1px solid #e2e8f0; text-align: right;">
                <button onclick="closeMemberDetails()" class="btn-cancel" style="padding: 8px 20px; border: 1px solid #cbd5e1; border-radius: 4px; font-weight: bold; cursor: pointer; color: #475569; background-color: white; transition: background-color 0.15s ease;">{{ __('Close / बंद करें') }}</button>
            </div>
        </div>
    </div>

    <!-- Client-side script logic -->
    <script>
        // District filter
        function filterMembersByDistrict(district) {
            const rows = document.querySelectorAll('.member-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const rowDistrict = row.getAttribute('data-district');
                if (!district || rowDistrict === district) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Update row indexes dynamically
            let index = 1;
            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    row.querySelector('.row-index').textContent = index++;
                }
            });

            // Handle no results
            const emptyRow = document.getElementById('no-filter-results-row');
            if (visibleCount === 0) {
                if (!emptyRow) {
                    const tbody = document.querySelector('.data-table tbody');
                    const tr = document.createElement('tr');
                    tr.id = 'no-filter-results-row';
                    tr.innerHTML = `<td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted); font-weight: 500;">📭 {{ __('No members found for the selected district.') }}</td>`;
                    tbody.appendChild(tr);
                }
            } else {
                if (emptyRow) {
                    emptyRow.remove();
                }
            }
        }

        // Details Modal popups
        function openMemberDetails(member) {
            document.getElementById('modalMemberName').textContent = member.name;
            document.getElementById('modalMemberDesignation').textContent = member.designation;
            document.getElementById('modalMemberState').textContent = member.state;
            document.getElementById('modalMemberDistrict').textContent = member.district;
            
            // Set image or SVG placeholder
            const photoEl = document.getElementById('modalMemberPhoto');
            if (member.photo && member.photo.trim() !== '') {
                photoEl.src = member.photo;
            } else {
                // Vector user silhouette SVG data URI
                photoEl.src = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="110" height="135" viewBox="0 0 24 24" fill="none" stroke="%2394a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="background:%23f1f5f9;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
            }
            
            // Show/hide contact details container
            const contactContainer = document.getElementById('modalMemberContactContainer');
            if (member.contact && member.contact.trim() !== '') {
                document.getElementById('modalMemberContact').textContent = member.contact;
                contactContainer.style.display = 'block';
            } else {
                contactContainer.style.display = 'none';
            }

            // Show/hide PDF URL link
            const pdfContainer = document.getElementById('modalMemberPdfContainer');
            if (member.pdf_url && member.pdf_url.trim() !== '') {
                document.getElementById('modalMemberPdfUrl').href = member.pdf_url;
                pdfContainer.style.display = 'block';
            } else {
                pdfContainer.style.display = 'none';
            }

            const modal = document.getElementById('memberDetailsModal');
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.style.opacity = '1';
                modal.querySelector('.modal-container').style.transform = 'scale(1)';
            }, 10);
        }

        function closeMemberDetails() {
            const modal = document.getElementById('memberDetailsModal');
            modal.style.opacity = '0';
            modal.querySelector('.modal-container').style.transform = 'scale(0.95)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 200);
        }

        // Gallery Lightbox functions
        function openGalleryLightbox(data) {
            const lightbox = document.getElementById('galleryLightbox');
            const mediaContainer = document.getElementById('lightboxMediaContainer');
            const titleElem = document.getElementById('lightboxTitle');
            const descElem = document.getElementById('lightboxDescription');

            titleElem.textContent = data.title;
            descElem.textContent = data.description || '';

            mediaContainer.innerHTML = ''; // Clear

            if (data.type === 'video') {
                let videoUrl = data.media_url;
                let ytId = getYoutubeIdJs(videoUrl);

                if (ytId) {
                    mediaContainer.innerHTML = `
                        <iframe src="https://www.youtube.com/embed/${ytId}?autoplay=1&rel=0" 
                                style="width: 100%; aspect-ratio: 16/9; border: none; max-height: 70vh; width: 100%;" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen></iframe>
                    `;
                } else {
                    mediaContainer.innerHTML = `
                        <video src="${videoUrl}" autoplay controls style="max-width: 100%; max-height: 70vh; outline: none; width: 100%;"></video>
                    `;
                }
            } else {
                mediaContainer.innerHTML = `
                    <img src="${data.media_url}" alt="${data.title}" style="max-width: 100%; max-height: 70vh; object-fit: contain;">
                `;
            }

            lightbox.style.display = 'flex';
            setTimeout(() => {
                lightbox.style.opacity = '1';
            }, 10);
        }

        function closeGalleryLightbox() {
            const lightbox = document.getElementById('galleryLightbox');
            const mediaContainer = document.getElementById('lightboxMediaContainer');
            
            lightbox.style.opacity = '0';
            setTimeout(() => {
                lightbox.style.display = 'none';
                mediaContainer.innerHTML = ''; // Stop video playback
            }, 300);
        }

        function getYoutubeIdJs(url) {
            const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
            const match = url.match(regExp);
            return (match && match[2].length === 11) ? match[2] : null;
        }

        // Close lightbox on click outside media container
        document.getElementById('galleryLightbox').addEventListener('click', function(e) {
            if (e.target === this) {
                closeGalleryLightbox();
            }
        });
    </script>

    <!-- Gallery Lightbox Modal -->
    <div id="galleryLightbox" class="lightbox-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(8px); z-index: 99999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease-in-out;">
        <button onclick="closeGalleryLightbox()" style="position: absolute; top: 20px; right: 25px; background: none; border: none; font-size: 2.5rem; color: white; cursor: pointer; opacity: 0.8; transition: opacity 0.2s; z-index: 100000;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">&times;</button>
        
        <div style="width: 90%; max-width: 900px; display: flex; flex-direction: column; gap: 15px; align-items: center;">
            <!-- Media Container -->
            <div id="lightboxMediaContainer" style="width: 100%; max-height: 70vh; display: flex; justify-content: center; align-items: center; background: black; border-radius: 8px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                <!-- Dynamic Content (Img or Video) -->
            </div>

            <!-- Details Container -->
            <div style="color: white; text-align: center; max-width: 700px; margin-top: 10px; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                <h3 id="lightboxTitle" style="font-size: 1.3rem; margin: 0 0 8px; font-weight: bold; color: var(--accent-gold);"></h3>
                <p id="lightboxDescription" style="font-size: 0.9rem; margin: 0; color: #cbd5e1; line-height: 1.5;"></p>
            </div>
        </div>
    </div>

    <style>
        .gallery-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
            border-color: var(--accent-gold) !important;
        }
        .gallery-card:hover img {
            transform: scale(1.05);
        }
        .gallery-card:hover .video-play-overlay {
            background: rgba(15, 23, 42, 0.2) !important;
        }
        .gallery-card:hover .play-button-circle {
            transform: scale(1.1) !important;
            background-color: var(--primary-color) !important;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.4) !important;
        }
        .gallery-card:hover .photo-zoom-overlay {
            background: rgba(15, 23, 42, 0.35) !important;
        }
        .gallery-card:hover .zoom-icon {
            opacity: 1 !important;
            transform: scale(1) !important;
        }
    </style>

@endsection
