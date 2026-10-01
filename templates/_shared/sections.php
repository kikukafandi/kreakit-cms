<?php
/**
 * Company-profile sections shared by every template (about, highlights, gallery, testimonials, FAQ, hours).
 * Content comes from page_sections(); each block renders nothing when its content is empty.
 * Styling reads the host template's CSS variables, so the blocks pick up its colours, font and corner radius.
 */

declare(strict_types=1);

function kit_sections_data(array $settings): array
{
    $sections = page_sections($settings);
    $sections['about']['image'] = public_upload_url($sections['about']['image']);
    $sections['gallery'] = array_map('public_upload_url', $sections['gallery']);
    return $sections;
}

function kit_paragraphs(string $text): string
{
    $blocks = preg_split('/\n\s*\n/', trim($text)) ?: [];
    return implode('', array_map(static fn (string $block): string => '<p>' . nl2br(e(trim($block))) . '</p>', $blocks));
}

function kit_about(array $kit): void
{
    $about = $kit['sections']['about'];
    $stats = $kit['sections']['stats'];
    if ($about['body'] === '' && $stats === []) {
        return;
    }
    ?>
    <section class="ks ks-about-wrap" id="tentang">
        <div class="wrap ks-about <?= $about['image'] === '' ? 'no-image' : '' ?>" data-reveal>
            <?php if ($about['image'] !== ''): ?><img class="ks-about-img" src="<?= e($about['image']) ?>" alt="<?= e($about['title'] ?: $kit['name']) ?>" loading="lazy"><?php endif; ?>
            <div>
                <h2 class="ks-title"><?= e($about['title'] ?: 'Tentang ' . $kit['name']) ?></h2>
                <div class="ks-body"><?= kit_paragraphs($about['body']) ?></div>
                <?php if ($stats !== []): ?>
                    <dl class="ks-stats">
                        <?php foreach ($stats as $stat): ?><div><dt><?= e($stat['label']) ?></dt><dd><?= e($stat['value']) ?></dd></div><?php endforeach; ?>
                    </dl>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
}

function kit_highlights(array $kit): void
{
    $highlights = $kit['sections']['highlights'];
    if ($highlights === []) {
        return;
    }
    ?>
    <section class="ks" id="keunggulan">
        <div class="wrap" data-reveal>
            <h2 class="ks-title">Kenapa pilih <?= e($kit['name']) ?></h2>
            <div class="ks-highlights">
                <?php foreach ($highlights as $highlight): ?>
                    <div class="ks-highlight">
                        <span class="ks-highlight-icon"><?= kit_icon('seal') ?></span>
                        <div><h3><?= e($highlight['title']) ?></h3><?php if ($highlight['body'] !== ''): ?><p><?= e($highlight['body']) ?></p><?php endif; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function kit_gallery(array $kit): void
{
    $gallery = array_slice($kit['sections']['gallery'], 0, 6);
    if ($gallery === []) {
        return;
    }
    ?>
    <section class="ks" id="galeri">
        <div class="wrap" data-reveal>
            <h2 class="ks-title">Galeri</h2>
            <div class="ks-gallery ks-gallery-<?= count($gallery) ?>">
                <?php foreach ($gallery as $index => $image): ?>
                    <a href="<?= e($image) ?>" target="_blank" rel="noopener" class="ks-gallery-item"><img src="<?= e($image) ?>" alt="Foto <?= $index + 1 ?> <?= e($kit['name']) ?>" loading="lazy"></a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function kit_testimonials(array $kit): void
{
    $testimonials = $kit['sections']['testimonials'];
    if ($testimonials === []) {
        return;
    }
    ?>
    <section class="ks" id="testimoni">
        <div class="wrap" data-reveal>
            <h2 class="ks-title">Kata pelanggan</h2>
            <div class="ks-testimonials">
                <?php foreach ($testimonials as $testimonial): ?>
                    <figure class="ks-testimonial">
                        <span class="ks-quote-mark"><?= kit_icon('quote') ?></span>
                        <blockquote><?= e($testimonial['quote']) ?></blockquote>
                        <figcaption>
                            <span class="ks-avatar"><?= e(kit_initials($testimonial['name'] ?: '?')) ?></span>
                            <span><b><?= e($testimonial['name']) ?></b><?php if ($testimonial['role'] !== ''): ?><small><?= e($testimonial['role']) ?></small><?php endif; ?></span>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function kit_faq(array $kit): void
{
    $faq = $kit['sections']['faq'];
    if ($faq === []) {
        return;
    }
    ?>
    <section class="ks" id="faq">
        <div class="wrap ks-faq-wrap" data-reveal>
            <h2 class="ks-title">Pertanyaan yang sering ditanyakan</h2>
            <div class="ks-faq">
                <?php foreach ($faq as $entry): ?>
                    <details>
                        <summary><?= e($entry['question']) ?><span><?= kit_icon('caret') ?></span></summary>
                        <div class="ks-body"><?= kit_paragraphs($entry['answer']) ?></div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Opening hours as one contact row; templates place it inside their own contact block.
 */
function kit_hours(array $kit, string $class = 'ks-hours'): string
{
    $hours = $kit['sections']['hours'];
    return $hours === '' ? '' : '<div class="' . e($class) . '">' . kit_icon('clock') . '<div><b>Jam buka</b><span>' . nl2br(e($hours)) . '</span></div></div>';
}

function kit_sections_css(): string
{
    return <<<'CSS'
.ks{padding:88px 0}.ks-title{font-size:clamp(28px,3.2vw,40px);line-height:1.1;margin:0 0 28px;text-wrap:balance}
.ks-body p{margin:0 0 14px;color:var(--muted);line-height:1.7;font-size:16.5px}.ks-body p:last-child{margin-bottom:0}
.ks-about{display:grid;grid-template-columns:.9fr 1.1fr;gap:64px;align-items:center}.ks-about.no-image{grid-template-columns:1fr;max-width:760px}
.ks-about-img{width:100%;aspect-ratio:4/5;object-fit:cover;border-radius:var(--card-radius);box-shadow:0 30px 70px color-mix(in srgb,var(--ink) 14%,transparent)}
.ks-about .ks-title{margin-bottom:18px}
.ks-stats{display:flex;flex-wrap:wrap;gap:20px 44px;margin:32px 0 0;padding-top:28px;border-top:1px solid var(--line)}
.ks-stats div{display:flex;flex-direction:column-reverse}.ks-stats dt{color:var(--muted);font-size:14px;margin-top:4px}.ks-stats dd{margin:0;font-size:clamp(28px,3vw,38px);font-weight:700;letter-spacing:-.03em;color:var(--ink)}
.ks-highlights{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 48px}
.ks-highlight{display:flex;gap:18px;padding:24px 0;border-top:1px solid var(--line)}
.ks-highlight-icon{flex:none;width:46px;height:46px;border-radius:var(--radius);display:grid;place-items:center;background:var(--tint);color:var(--accent);font-size:22px}
.ks-highlight h3{margin:0 0 6px;font-size:18px;letter-spacing:-.015em}.ks-highlight p{margin:0;color:var(--muted);line-height:1.6}
.ks-gallery{display:grid;gap:14px;grid-template-columns:repeat(4,1fr);grid-auto-rows:220px}
.ks-gallery-item{display:block;overflow:hidden;border-radius:var(--card-radius);background:var(--tint)}
.ks-gallery-item img{width:100%;height:100%;object-fit:cover;transition:transform .6s cubic-bezier(.16,1,.3,1)}.ks-gallery-item:hover img{transform:scale(1.04)}
/* Exact cell counts (max 6 photos): the first photo grows only where that fills the grid without holes. */
.ks-gallery-1{grid-template-columns:1fr;grid-auto-rows:420px}.ks-gallery-2{grid-template-columns:1fr 1fr;grid-auto-rows:340px}.ks-gallery-3{grid-template-columns:2fr 1fr}.ks-gallery-3 .ks-gallery-item:first-child{grid-row:span 2}
.ks-gallery-4{grid-template-columns:repeat(4,1fr);grid-auto-rows:300px}.ks-gallery-5 .ks-gallery-item:first-child{grid-column:span 2;grid-row:span 2}.ks-gallery-6{grid-template-columns:repeat(3,1fr)}.ks-gallery-6 .ks-gallery-item:first-child{grid-column:span 2;grid-row:span 2}
.ks-testimonials{display:grid;grid-auto-flow:column;grid-auto-columns:min(380px,86%);gap:18px;overflow-x:auto;scroll-snap-type:x mandatory;scrollbar-width:none;padding:4px 2px 8px}
.ks-testimonial{margin:0;scroll-snap-align:start;background:var(--surface);border-radius:var(--card-radius);box-shadow:inset 0 0 0 1px var(--line);padding:28px;display:flex;flex-direction:column;gap:18px}
.ks-quote-mark{font-size:28px;color:var(--accent)}.ks-testimonial blockquote{margin:0;font-size:17px;line-height:1.6;display:-webkit-box;-webkit-line-clamp:5;-webkit-box-orient:vertical;overflow:hidden}
.ks-testimonial figcaption{margin-top:auto;display:flex;align-items:center;gap:12px}.ks-testimonial b{display:block;font-size:15px}.ks-testimonial small{color:var(--muted);font-size:13.5px}
.ks-avatar{width:42px;height:42px;border-radius:50%;display:grid;place-items:center;background:var(--tint);color:var(--accent);font-weight:700;font-size:14px;flex:none}
.ks-faq-wrap{max-width:820px}.ks-faq details{border-top:1px solid var(--line)}.ks-faq details:last-child{border-bottom:1px solid var(--line)}
.ks-faq summary{list-style:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:16px;padding:20px 0;font-weight:600;font-size:17px}.ks-faq summary::-webkit-details-marker{display:none}
.ks-faq summary span{flex:none;display:grid;transition:transform .25s}.ks-faq details[open] summary span{transform:rotate(180deg)}.ks-faq .ks-body{padding:0 40px 22px 0}
.ks-hours{display:flex;gap:14px;align-items:flex-start;line-height:1.55}.ks-hours .icon{width:22px;height:22px;color:var(--accent);margin-top:1px}.ks-hours b{display:block}.ks-hours span{color:var(--muted)}
@media(max-width:900px){.ks{padding:64px 0}.ks-about{grid-template-columns:1fr;gap:32px}.ks-about-img{aspect-ratio:4/3}.ks-highlights{grid-template-columns:1fr}
.ks-gallery{grid-template-columns:1fr 1fr!important;grid-auto-rows:170px!important}.ks-gallery .ks-gallery-item{grid-column:auto!important;grid-row:auto!important}.ks-gallery-1{grid-template-columns:1fr!important;grid-auto-rows:240px!important}.ks-gallery-3 .ks-gallery-item:first-child,.ks-gallery-5 .ks-gallery-item:first-child{grid-column:span 2!important}}
CSS;
}
