<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VIGYANMEV JAYATE - विज्ञानमेव जयते') | National Hindi-English Scientific Magazine of India</title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        /* Embedded styling helper for accessibility font resizing */
        body.font-lg { font-size: 1.15rem; }
        body.font-sm { font-size: 0.9rem; }
        body.contrast-mode {
            background-color: #121212 !important;
            color: #f1f5f9 !important;
        }
        body.contrast-mode .main-header,
        body.contrast-mode .news-marquee-section,
        body.contrast-mode .news-card,
        body.contrast-mode .sidebar-panel,
        body.contrast-mode .article-container,
        body.contrast-mode .payment-card,
        body.contrast-mode .data-table-wrapper {
            background-color: #1e1e1e !important;
            color: #f1f5f9 !important;
            border-color: #334155 !important;
        }
        body.contrast-mode h1,
        body.contrast-mode h2,
        body.contrast-mode h3,
        body.contrast-mode h4,
        body.contrast-mode td {
            color: #ffffff !important;
        }
        body.contrast-mode .data-table tr:nth-child(even) {
            background-color: #262626 !important;
        }
    </style>
</head>
<body>

    <!-- 1. Government Top Accent -->
    <div class="gov-top-bar"></div>

    <!-- 2. Accessibility & Top Utility Bar -->
    <div class="top-bar-links">
        <div class="container">
            <div>
                <a href="#main-content">{{ __('Skip to main content') }}</a>
                <span>{{ app()->getLocale() == 'hi' ? 'भारत सरकार' : 'GOVERNMENT OF INDIA' }}</span>
            </div>
            <div class="accessibility-tools">
                <a href="{{ route('lang.switch', 'hi') }}" style="color: {{ app()->getLocale() == 'hi' ? 'var(--accent-gold)' : '#cbd5e1' }}; font-weight: bold; margin-right: 8px;">हिन्दी</a>
                <span style="color: #475569;">|</span>
                <a href="{{ route('lang.switch', 'en') }}" style="color: {{ app()->getLocale() == 'en' ? 'var(--accent-gold)' : '#cbd5e1' }}; font-weight: bold; margin-left: 8px; margin-right: 15px;">English</a>
                <button class="btn-acc" onclick="changeFontSize('sm')">A-</button>
                <button class="btn-acc" onclick="changeFontSize('md')">A</button>
                <button class="btn-acc" onclick="changeFontSize('lg')">A+</button>
                <button class="btn-acc" onclick="toggleContrast()">High Contrast ◐</button>
                <a href="/admin/login" style="margin-left: 15px; color: var(--accent-gold); font-weight: bold;">{{ __('Admin Login') }} 🔑</a>
            </div>
        </div>
    </div>

    <!-- 3. Government Branding Header -->
    <header class="main-header">
        <div class="container header-grid">
            <div class="header-left" style="display: flex; align-items: center; gap: 20px;">
                <!-- Circular Blue Website Logo -->
                <img src="/images/logo.png" alt="Vigyanmev Logo" style="height: 85px; width: 85px; border-radius: 50%; object-fit: cover; border: 2px solid #ffffff; box-shadow: var(--shadow-sm);">
                <div class="logo-text-block" style="padding-left: 0;">
                    <h1>{{ __('VIGYANMEV JAYATE') }}</h1>
                    <div class="sub-heading">{{ __('Vigyanmev Jayate') }} • {{ __('NATIONAL SCIENTIFIC MAGAZINE OF INDIA') }}</div>
                    <div class="ministry-text">{{ __('National Hindi-English Science & Technology Publication') }}</div>
                </div>
            </div>
            <div class="header-right" style="display: flex; align-items: center; gap: 20px;">
                <!-- Circular Blue Ashoka Emblem Logo -->
                <img src="/images/ashoka.png" alt="Ashoka Emblem" style="height: 85px; width: 85px; border-radius: 50%; object-fit: cover; display: block; border: 2px solid #ffffff; box-shadow: var(--shadow-sm);">
            </div>
        </div>
    </header>

    <!-- 4. Navigation Menu -->
    <nav class="main-navbar" id="main-navbar">
        <div class="container nav-container">
            <!-- Hamburger for mobile -->
            <button class="hamburger-btn" id="hamburger-btn" aria-label="Toggle menu" onclick="toggleMobileMenu()">
                <span></span><span></span><span></span>
            </button>

            <ul class="nav-list" id="nav-list">
                <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link">{{ __('Home') }}</a>
                </li>

                @if(isset($navigationMenus))
                    @foreach($navigationMenus as $menu)
                        @php
                            $title = app()->getLocale() == 'hi' ? $menu->title_hi : $menu->title_en;
                        @endphp

                        @if($menu->type === 'parent')
                            <li class="nav-item has-dropdown">
                                <a href="#" class="nav-link" onclick="toggleDropdown(event, this)">{{ $title }} ▾</a>
                                @if($menu->publishedChildren && count($menu->publishedChildren) > 0)
                                    <ul class="dropdown-menu">
                                        @foreach($menu->publishedChildren as $child)
                                            @php
                                                $childTitle = app()->getLocale() == 'hi' ? $child->title_hi : $child->title_en;
                                                if ($child->type === 'page') {
                                                    $childUrl = route('pages.show', $child->slug);
                                                } elseif ($child->type === 'directory') {
                                                    $childUrl = route('directory.show', $child->directory_category);
                                                } else {
                                                    $childUrl = $child->url;
                                                }
                                            @endphp
                                            <li class="dropdown-item">
                                                <a href="{{ $childUrl }}">{{ $childTitle }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @else
                            @php
                                if ($menu->type === 'page') {
                                    $menuUrl = route('pages.show', $menu->slug);
                                } elseif ($menu->type === 'directory') {
                                    $menuUrl = route('directory.show', $menu->directory_category);
                                } else {
                                    $menuUrl = $menu->url;
                                }
                            @endphp
                            <li class="nav-item">
                                <a href="{{ $menuUrl }}" class="nav-link">{{ $title }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            </ul>
        </div>
    </nav>

    <!-- 5. Main Content Wrapper -->
    <main id="main-content">
        @yield('content')
    </main>

    <!-- 6. Footer Section -->
    @php
        use App\Models\Setting;
        $footerAbout    = Setting::get('footer_about_text', 'National Hindi-English Scientific Magazine of India.');
        $footerUseful   = json_decode(Setting::get('footer_useful_links', '[]'), true) ?: [];
        $footerDirs     = json_decode(Setting::get('footer_directory_links', '[]'), true) ?: [];
        $footerConName  = Setting::get('footer_contact_name', 'Vigyanmev Jayate Press Club Head Office');
        $footerConCity  = Setting::get('footer_contact_city', 'New Delhi, India');
        $footerConEmail = Setting::get('footer_contact_email', 'contact@vigyanmev.gov.in');
        $footerConPhone = Setting::get('footer_contact_phone', '+91-11-23091122');
        $footerCopy     = Setting::get('copyright_text', '© ' . date('Y') . ' VIGYANMEV JAYATE. All Rights Reserved.');
        $siteName       = Setting::get('site_name', 'Vigyanmev Jayate');
        $socialFB       = Setting::get('social_facebook', '');
        $socialTW       = Setting::get('social_twitter', '');
        $socialYT       = Setting::get('social_youtube', '');
        $socialIG       = Setting::get('social_instagram', '');
    @endphp
    <footer class="main-footer">
        <div class="container footer-grid">
            <div class="footer-widget">
                <h4>{{ $siteName }}</h4>
                <p>{{ $footerAbout }}</p>
                @if($socialFB || $socialTW || $socialYT || $socialIG)
                    <div style="display: flex; gap: 12px; margin-top: 16px; flex-wrap: wrap;">
                        @if($socialFB)<a href="{{ $socialFB }}" target="_blank" rel="noopener" style="color: #cbd5e1; font-size: 1.4rem;">🔵</a>@endif
                        @if($socialTW)<a href="{{ $socialTW }}" target="_blank" rel="noopener" style="color: #cbd5e1; font-size: 1.4rem;">🐦</a>@endif
                        @if($socialYT)<a href="{{ $socialYT }}" target="_blank" rel="noopener" style="color: #cbd5e1; font-size: 1.4rem;">▶️</a>@endif
                        @if($socialIG)<a href="{{ $socialIG }}" target="_blank" rel="noopener" style="color: #cbd5e1; font-size: 1.4rem;">📸</a>@endif
                    </div>
                @endif
            </div>
            <div class="footer-widget">
                <h4>Useful Links</h4>
                <ul class="footer-links">
                    @forelse($footerUseful as $link)
                        <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                    @empty
                        <li><a href="{{ route('home') }}">Home</a></li>
                    @endforelse
                </ul>
            </div>
            <div class="footer-widget">
                <h4>Directories</h4>
                <ul class="footer-links">
                    @forelse($footerDirs as $dir)
                        <li><a href="{{ route('directory.show', $dir['slug']) }}">{{ $dir['label'] }}</a></li>
                    @empty
                        <li><a href="{{ route('home') }}">Home</a></li>
                    @endforelse
                </ul>
            </div>
            <div class="footer-widget">
                <h4>Contact Office</h4>
                <p><strong>{{ $footerConName }}</strong></p>
                <p>{{ $footerConCity }}</p>
                @if($footerConEmail)<p>Email: {{ $footerConEmail }}</p>@endif
                @if($footerConPhone)<p>Phone: {{ $footerConPhone }}</p>@endif
            </div>
        </div>
        <div class="container footer-bottom">
            <p>{{ $footerCopy }}</p>
        </div>
    </footer>

    <!-- Accessibility + Mobile Menu JS -->
    <script>
        function changeFontSize(size) {
            document.body.classList.remove('font-sm', 'font-lg');
            if (size === 'sm') document.body.classList.add('font-sm');
            else if (size === 'lg') document.body.classList.add('font-lg');
        }

        function toggleContrast() {
            document.body.classList.toggle('contrast-mode');
        }

        // Mobile hamburger menu
        function toggleMobileMenu() {
            const navList = document.getElementById('nav-list');
            const btn = document.getElementById('hamburger-btn');
            navList.classList.toggle('nav-open');
            btn.classList.toggle('is-open');
        }

        // Mobile: toggle dropdowns on tap instead of hover
        function toggleDropdown(e, el) {
            if (window.innerWidth <= 900) {
                e.preventDefault();
                const li = el.closest('.has-dropdown');
                li.classList.toggle('dropdown-open');
            }
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            const nav = document.getElementById('nav-list');
            const btn = document.getElementById('hamburger-btn');
            if (nav && btn && !nav.contains(e.target) && !btn.contains(e.target)) {
                nav.classList.remove('nav-open');
                btn.classList.remove('is-open');
            }
        });
    </script>
</body>
</html>
