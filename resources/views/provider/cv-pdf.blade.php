<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curriculum Vitae - {{ $seeker->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/cv-pdf.css') }}">
</head>
<body>

    @php
        // ── Clean preferred_role: strip HTML tags, extract meaningful comma-separated roles
        $rawRole = trim(strip_tags(html_entity_decode($seeker->preferred_role ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        // Re-split by comma or newline and rejoin cleanly
        $cleanRoles = array_values(array_filter(array_map('trim', preg_split('/[,\n]+/', $rawRole))));
        $displayRole = !empty($cleanRoles) ? implode(', ', $cleanRoles) : 'Professional Candidate';
        $primaryRole = !empty($cleanRoles) ? $cleanRoles[0] : 'Professional Candidate';

        // ── Portfolio: detect if it's a plain URL or rich HTML content
        $rawPortfolio   = $seeker->portfolio ?? '';
        $plainPortfolio = trim(strip_tags(html_entity_decode($rawPortfolio, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        $isPortfolioUrl = !empty($plainPortfolio) && preg_match('/^https?:\/\//i', trim($plainPortfolio));
        $portfolioUrl   = $isPortfolioUrl ? trim($plainPortfolio) : null;
        $hasPortfolioContent = !empty($plainPortfolio) && !$isPortfolioUrl;
    @endphp

    {{-- CV CONTAINER (Standard A4 Format) --}}
    <div class="cv-container">

        {{-- HEADER SECTION --}}
        <header class="cv-header">
            <div class="cv-header-left">
                <h1 class="cv-name">{{ $seeker->name }}</h1>
                <div class="cv-role">{{ $displayRole }}</div>

                @if($portfolioUrl)
                <a href="{{ $portfolioUrl }}" target="_blank" class="cv-link-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    {{ $portfolioUrl }}
                </a>
                @endif
            </div>

            <div class="cv-header-right">
                @if($seeker->location)
                <div class="cv-contact-item">
                    <span>{{ trim(strip_tags($seeker->location)) }}</span>
                    <svg class="cv-contact-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                @endif

                @if($seeker->phone)
                <div class="cv-contact-item">
                    <span>{{ trim(strip_tags($seeker->phone)) }}</span>
                    <svg class="cv-contact-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </div>
                @endif

                <div class="cv-contact-item">
                    <span>{{ $seeker->email }}</span>
                    <svg class="cv-contact-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </header>

        {{-- PROFILE SUMMARY SECTION --}}
        @php $summaryText = $cvSummary ?? $seeker->generateCvSummary(); @endphp
        @if($summaryText)
        <section class="cv-section">
            <h2 class="cv-section-title">Profile Summary</h2>
            <div class="cv-summary-text">
                {{ $summaryText }}
            </div>
        </section>
        @endif

        {{-- WORK EXPERIENCE SECTION --}}
        @php $expContent = trim(strip_tags($seeker->experience_years ?? '')); @endphp
        <section class="cv-section">
            <h2 class="cv-section-title">Work Experience</h2>
            @if($expContent)
                <div class="cv-item">
                    <div class="cv-item-details">
                        {!! $seeker->experience_years !!}
                    </div>
                </div>
            @else
                <div class="cv-item">
                    <div class="cv-item-header">
                        <span class="cv-item-title">{{ $primaryRole }}</span>
                        <span class="cv-item-date">Present</span>
                    </div>
                    <div class="cv-item-details">
                        <p>Relevant professional background and experience.</p>
                    </div>
                </div>
            @endif
        </section>

        {{-- SKILLS SECTION (2 COLUMNS) --}}
        @if(!empty(trim(strip_tags($seeker->skills ?? ''))))
        <section class="cv-section">
            <h2 class="cv-section-title">Skills</h2>
            <div class="cv-skills-content">
                {!! $seeker->skills !!}
            </div>
        </section>
        @endif

        {{-- EDUCATION SECTION --}}
        <section class="cv-section">
            <h2 class="cv-section-title">Education</h2>
            @if($seeker->educations && $seeker->educations->isNotEmpty())
                @foreach($seeker->educations as $edu)
                <div class="cv-item">
                    <div class="cv-item-header">
                        <span class="cv-item-title">{{ $edu->degree }}</span>
                        <span class="cv-item-date">{{ $edu->start_year }} &ndash; {{ $edu->end_year }}</span>
                    </div>
                    <div class="cv-item-subtitle">{{ $edu->school }}</div>
                    @if($edu->field_of_study)
                    <div class="cv-item-details" style="color:#475569; padding-left:0;">
                        {{ $edu->field_of_study }}
                    </div>
                    @endif
                </div>
                @endforeach
            @elseif($seeker->education)
                <div class="cv-item">
                    <span class="cv-item-title">{{ trim(strip_tags($seeker->education)) }}</span>
                </div>
            @else
                <div class="cv-summary-text">Academic background listed on candidate profile.</div>
            @endif
        </section>

        {{-- PROJECTS & PORTFOLIO SECTION --}}
        {{-- Only render if portfolio is rich HTML content (not a plain URL) --}}
        @if($hasPortfolioContent)
        <section class="cv-section">
            <h2 class="cv-section-title">Projects &amp; Portfolio</h2>
            <div class="cv-item-details" style="padding-left:0;">
                {!! $seeker->portfolio !!}
            </div>
        </section>
        @endif

    </div>

<script>
/**
 * CV Page-Break Fix
 * ─────────────────
 * Chrome's print engine ignores CSS orphans/widows for block-level elements.
 * This script physically wraps:
 *   1. Each section TITLE + its FIRST content block → so the title never sits
 *      alone at the bottom of a page without at least some content.
 *   2. Each skill CATEGORY HEADING + its bullet list → so "Programming Languages"
 *      never appears at the bottom without its items.
 */
document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Keep every section title glued to its first content sibling ──────
    document.querySelectorAll('.cv-section').forEach(function (section) {
        var title = section.querySelector('.cv-section-title');
        if (!title) return;
        var firstContent = title.nextElementSibling;
        if (!firstContent) return;

        // Wrap title + firstContent together in a no-break container
        var anchor = document.createElement('div');
        anchor.style.cssText = 'break-inside:avoid;page-break-inside:avoid;-webkit-column-break-inside:avoid;';
        section.insertBefore(anchor, title);
        anchor.appendChild(title);

        // Clone first content, insert into anchor, remove original
        var firstClone = firstContent.cloneNode(true);
        anchor.appendChild(firstClone);
        firstContent.parentNode.removeChild(firstContent);
    });

    // ── 2. Group skill category headings with their bullet lists ────────────
    var skillsEl = document.querySelector('.cv-skills-content');
    if (skillsEl) {
        var nodes   = Array.from(skillsEl.childNodes);
        var rebuilt = document.createDocumentFragment();
        var i = 0;

        while (i < nodes.length) {
            var node = nodes[i];

            // Detect a category heading: <p> or <div> containing <strong> or <b>
            var isHeading = (node.nodeType === 1) &&
                            ['P', 'DIV'].includes(node.tagName) &&
                            node.querySelector('strong, b');

            if (isHeading) {
                // Wrap this heading + all following non-heading siblings until next heading
                var group = document.createElement('div');
                group.style.cssText = 'break-inside:avoid;page-break-inside:avoid;-webkit-column-break-inside:avoid;';
                group.appendChild(node.cloneNode(true));
                i++;
                while (i < nodes.length) {
                    var next = nodes[i];
                    var nextIsHeading = (next.nodeType === 1) &&
                                        ['P', 'DIV'].includes(next.tagName) &&
                                        next.querySelector('strong, b');
                    if (nextIsHeading) break;
                    group.appendChild(next.cloneNode(true));
                    i++;
                }
                rebuilt.appendChild(group);
            } else {
                rebuilt.appendChild(node.cloneNode(true));
                i++;
            }
        }

        skillsEl.innerHTML = '';
        skillsEl.appendChild(rebuilt);
    }
});
</script>

</body>
</html>
