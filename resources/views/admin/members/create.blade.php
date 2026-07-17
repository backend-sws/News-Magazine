@extends('layouts.admin')

@section('header_title', 'Add New Directory / Press Club Member')

@section('admin_content')

    <div class="admin-card" style="max-width: 750px; margin: 0 auto;">
        <div style="margin-bottom: 25px; border-bottom: 1px solid var(--border-color); padding-bottom: 15px;">
            <h3 style="color: var(--primary-color);">Add Committee / Board Member</h3>
            <p style="font-size: 0.8rem; color: var(--text-muted);">Assign the profile to any of the 37 categories, representing National Boards, state editors, subdivision press clubs, advocates, or local bureaus.</p>
        </div>

        @if($errors->any())
            <div style="background-color: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 4px; font-size: 0.85rem; margin-bottom: 25px; font-weight: 600;">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.members.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="category">Directory Category / श्रेणी *</label>
                    <select name="category" id="category" class="form-control" required onchange="toggleGalleryFields()">
                        <option value="">-- Select Board / Press Club Directory --</option>
                        @foreach($categories as $key => $value)
                            <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="locale">Language / भाषा *</label>
                    <select name="locale" id="locale" class="form-control" required>
                        <option value="en" {{ old('locale') == 'en' ? 'selected' : '' }}>English</option>
                        <option value="hi" {{ old('locale') == 'hi' ? 'selected' : '' }}>हिन्दी</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group" id="nameGroup">
                    <label for="name" id="nameLabel">Member Name / नाम</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter full name" required value="{{ old('name') }}">
                </div>

                <div class="form-group" id="designationGroup">
                    <label for="designation" id="designationLabel">Designation / पद</label>
                    <input type="text" name="designation" id="designation" class="form-control" placeholder="e.g. Chief Editor, President, Secretary" required value="{{ old('designation') }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;" id="locationFields">
                <div class="form-group">
                    <label for="state">State / राज्य (Optional)</label>
                    <input type="text" name="state" id="state" class="form-control" placeholder="e.g. Uttar Pradesh, Delhi" value="{{ old('state') }}">
                </div>

                <div class="form-group">
                    <label for="district">District / जिला (Optional)</label>
                    <input type="text" name="district" id="district" class="form-control" placeholder="e.g. Lucknow, Kanpur" value="{{ old('district') }}">
                </div>
            </div>

            <!-- Gallery Media Options -->
            <div id="galleryOptionsGroup" style="display: none; background-color: #f8fafc; border: 1px solid var(--border-color); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="media_type" style="font-weight: 700; color: var(--primary-color);">Media Type / मीडिया प्रकार *</label>
                    <select id="media_type" name="media_type" class="form-control" onchange="toggleMediaType()">
                        <option value="photo" {{ old('media_type') == 'photo' ? 'selected' : '' }}>🖼️ Photo - फोटो</option>
                        <option value="video" {{ old('media_type', 'photo') == 'video' ? 'selected' : '' }}>🎥 Video - वीडियो</option>
                    </select>
                </div>
            </div>

            <div class="form-group" id="photoField">
                <label for="photo" id="photoLabel">Member Photo / फोटो (Optional)</label>
                <div class="animated-file-upload">
                    <input type="file" name="photo" id="photo" accept="image/*">
                    <div class="file-upload-placeholder">
                        <span class="upload-icon">📁</span>
                        <span class="upload-text">Drag & drop or click to upload member photo</span>
                        <span class="upload-info">Allowed: jpeg, png, jpg, gif (Max: 5MB)</span>
                    </div>
                </div>
            </div>

            <!-- Video fields -->
            <div id="videoFields" style="display: none; margin-bottom: 20px; padding: 15px; border: 1px dashed #cbd5e1; border-radius: 8px; background-color: #fafafa;">
                <div class="form-group">
                    <label for="video" style="font-weight: bold;">Upload Video File / वीडियो फ़ाइल अपलोड करें (Optional)</label>
                    <div class="animated-file-upload">
                        <input type="file" name="video" id="video" accept="video/*">
                        <div class="file-upload-placeholder">
                            <span class="upload-icon">🎥</span>
                            <span class="upload-text">Drag & drop or click to upload video file</span>
                            <span class="upload-info">Allowed: mp4, webm, avi, mov (Max: 100MB)</span>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 15px; margin-bottom: 0;">
                    <label for="video_url" style="font-weight: bold;">Or Video Link (YouTube/Vimeo) / या वीडियो लिंक (यूट्यूब/विमियो)</label>
                    <input type="url" name="video_url" id="video_url" class="form-control" placeholder="e.g. https://www.youtube.com/watch?v=..." value="{{ old('video_url') }}">
                </div>
            </div>

            <div class="form-group" id="pdfField">
                <label for="pdf">Upload PDF Document / पीडीएफ दस्तावेज (Optional)</label>
                <div class="animated-file-upload">
                    <input type="file" name="pdf" id="pdf" accept=".pdf">
                    <div class="file-upload-placeholder">
                        <span class="upload-icon">📄</span>
                        <span class="upload-text">Drag & drop or click to upload PDF document</span>
                        <span class="upload-info">Optional PDF document linked directly in directory list (Max: 100MB)</span>
                    </div>
                </div>
            </div>

            <div class="form-group" id="contactField">
                <label for="contact_info">Contact & Address Details / संपर्क विवरण (Optional if PDF is uploaded)</label>
                <textarea name="contact_info" id="contact_info" rows="5" class="form-control" placeholder="Email: example@gmail.com&#10;Phone: +91-9876543210&#10;Office Address details..." style="resize: vertical;">{{ old('contact_info') }}</textarea>
            </div>

            <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                <a href="{{ route('admin.members.index') }}" class="btn-cancel">Cancel</a>
                <button type="submit" id="submitBtn" class="btn-primary">Save Member Profile</button>
            </div>

        </form>

        <script>
            function toggleGalleryFields() {
                const categorySelect = document.getElementById('category');
                const selectedCategory = categorySelect.value;
                const isGallery = (selectedCategory === 'photos-gallery' || selectedCategory === 'advertisements-gallery');

                const nameLabel = document.getElementById('nameLabel');
                const nameInput = document.getElementById('name');
                const designationLabel = document.getElementById('designationLabel');
                const designationInput = document.getElementById('designation');
                const submitBtn = document.getElementById('submitBtn');

                const locationFields = document.getElementById('locationFields');
                const pdfField = document.getElementById('pdfField');
                const contactField = document.getElementById('contactField');
                const galleryOptionsGroup = document.getElementById('galleryOptionsGroup');
                const photoField = document.getElementById('photoField');
                const photoLabel = document.getElementById('photoLabel');
                const videoFields = document.getElementById('videoFields');

                if (isGallery) {
                    nameLabel.innerText = "Media Title / शीर्षक *";
                    nameInput.placeholder = "Enter photo/video title";
                    designationLabel.innerText = "Caption & Description / विवरण";
                    designationInput.placeholder = "Enter description or caption details";
                    designationInput.removeAttribute('required');
                    submitBtn.innerText = "Save Gallery Media";

                    locationFields.style.display = 'none';
                    pdfField.style.display = 'none';
                    contactField.style.display = 'none';

                    galleryOptionsGroup.style.display = 'block';
                    photoLabel.innerText = "Upload Photo / फोटो अपलोड करें (Optional)";
                    
                    toggleMediaType();
                } else {
                    nameLabel.innerText = "Member Name / नाम *";
                    nameInput.placeholder = "Enter full name";
                    designationLabel.innerText = "Designation / पद *";
                    designationInput.placeholder = "e.g. Chief Editor, President, Secretary";
                    designationInput.setAttribute('required', 'required');
                    submitBtn.innerText = "Save Member Profile";

                    locationFields.style.display = 'grid';
                    pdfField.style.display = 'block';
                    contactField.style.display = 'block';
                    galleryOptionsGroup.style.display = 'none';
                    photoField.style.display = 'block';
                    photoLabel.innerText = "Member Photo / फोटो (Optional)";
                    videoFields.style.display = 'none';
                }
            }

            function toggleMediaType() {
                const mediaType = document.getElementById('media_type').value;
                const photoField = document.getElementById('photoField');
                const videoFields = document.getElementById('videoFields');

                if (mediaType === 'photo') {
                    photoField.style.display = 'block';
                    videoFields.style.display = 'none';
                } else {
                    photoField.style.display = 'none';
                    videoFields.style.display = 'block';
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                toggleGalleryFields();
            });
        </script>
    </div>

@endsection
