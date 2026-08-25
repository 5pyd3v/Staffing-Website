<svg class="hero-illustration" viewBox="0 0 560 520" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Illustration of candidates and employers being matched">
    <defs>
        <linearGradient id="heroBlob" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#0F5C57" stop-opacity="0.16"/>
            <stop offset="100%" stop-color="#1D6FA5" stop-opacity="0.10"/>
        </linearGradient>
        <linearGradient id="heroCardGrad" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#0F5C57"/>
            <stop offset="100%" stop-color="#0B4642"/>
        </linearGradient>
    </defs>

    <path class="hero-illustration__blob" d="M80 90C160 20 340 0 430 70C520 140 540 280 470 360C400 440 220 480 120 420C20 360 0 160 80 90Z" fill="url(#heroBlob)"/>

    <!-- connection lines -->
    <g stroke="#0F5C57" stroke-opacity="0.35" stroke-width="2" stroke-dasharray="5 7">
        <path class="hero-illustration__line" d="M175 200 L 300 150"/>
        <path class="hero-illustration__line" d="M175 200 L 250 320"/>
        <path class="hero-illustration__line" d="M300 150 L 420 230"/>
        <path class="hero-illustration__line" d="M250 320 L 400 360"/>
    </g>

    <!-- employer card -->
    <g class="hero-illustration__float" transform="translate(300,110)">
        <rect x="0" y="0" width="150" height="86" rx="14" fill="#FFFFFF" stroke="#E7E2D8" stroke-width="1.5"/>
        <rect x="16" y="18" width="34" height="34" rx="9" fill="#EAF2F0"/>
        <path d="M27 42V34h12v8M33 34v-8" stroke="#0F5C57" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        <rect x="60" y="22" width="70" height="9" rx="4.5" fill="#21201C" opacity="0.85"/>
        <rect x="60" y="38" width="50" height="7" rx="3.5" fill="#5B584F" opacity="0.55"/>
        <rect x="16" y="62" width="118" height="8" rx="4" fill="#E7E2D8"/>
    </g>

    <!-- candidate card -->
    <g class="hero-illustration__float hero-illustration__float--delay" transform="translate(95,175)">
        <rect x="0" y="0" width="150" height="96" rx="14" fill="#FFFFFF" stroke="#E7E2D8" stroke-width="1.5"/>
        <circle cx="34" cy="36" r="18" fill="#0F5C57" opacity="0.12"/>
        <circle cx="34" cy="30" r="7" fill="#0F5C57"/>
        <path d="M18 50c2-9 10-14 16-14s14 5 16 14" stroke="#0F5C57" stroke-width="2.2" stroke-linecap="round" fill="none"/>
        <rect x="62" y="20" width="72" height="9" rx="4.5" fill="#21201C" opacity="0.85"/>
        <rect x="62" y="36" width="55" height="7" rx="3.5" fill="#5B584F" opacity="0.55"/>
        <rect x="16" y="72" width="46" height="16" rx="8" fill="#EAF2F0"/>
        <rect x="68" y="72" width="46" height="16" rx="8" fill="#EAF2F0"/>
    </g>

    <!-- match badge -->
    <g class="hero-illustration__float hero-illustration__float--badge" transform="translate(215,290)">
        <circle cx="35" cy="35" r="35" fill="url(#heroCardGrad)"/>
        <path d="M21 36l10 10 20-22" stroke="#FAF8F4" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    </g>

    <!-- staffing request card -->
    <g class="hero-illustration__float hero-illustration__float--delay2" transform="translate(330,330)">
        <rect x="0" y="0" width="150" height="86" rx="14" fill="#FFFFFF" stroke="#E7E2D8" stroke-width="1.5"/>
        <rect x="16" y="16" width="118" height="9" rx="4.5" fill="#21201C" opacity="0.85"/>
        <rect x="16" y="32" width="90" height="7" rx="3.5" fill="#5B584F" opacity="0.55"/>
        <rect x="16" y="52" width="42" height="18" rx="9" fill="#FDF3DC"/>
        <text x="24" y="65" font-family="Inter, sans-serif" font-size="9" font-weight="700" fill="#8A5A00">NEW</text>
        <circle cx="118" cy="61" r="9" fill="#E4F3EA"/>
        <path d="M114 61l3 3 6-6.5" stroke="#2A7A4F" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    </g>

    <!-- sparkle accents -->
    <g fill="#1D6FA5" opacity="0.5">
        <path class="hero-illustration__sparkle" d="M470 130l4 10 10 4-10 4-4 10-4-10-10-4 10-4z"/>
        <path class="hero-illustration__sparkle hero-illustration__sparkle--delay" d="M60 300l3 7 7 3-7 3-3 7-3-7-7-3 7-3z"/>
    </g>
</svg>
