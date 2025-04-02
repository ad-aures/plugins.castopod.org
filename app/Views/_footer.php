<?php
$sections = [
    [
        'title' => lang('Common.footer.links'),
        'items' => [
            [
                'type'  => 'link',
                'href'  => 'mailto:hello@castopod.org',
                'label' => lang('Common.contactUs'),
            ],
            [
                'type'  => 'link',
                'href'  => 'https://code.castopod.org/adaures/castopod/issues',
                'label' => lang('Common.footer.issues'),
            ],
            [
                'type'  => 'link',
                'href'  => 'https://code.castopod.org/adaures/castopod/',
                'label' => lang('Common.sourceCode'),
            ],
            [
                'type'  => 'link',
                'href'  => 'https://github.com/ad-aures/castopod',
                'label' => 'Github [' . strtolower(lang('Common.mirror')) . ']', // @phpstan-ignore argument.type
            ],
            [
                'type'  => 'link',
                'href'  => 'https://blog.castopod.org/',
                'label' => lang('Common.blog'),
            ],
        ],
    ],
    [
        'title' => lang('Common.footer.community'),
        'items' => [
            [
                'type'  => 'link',
                'href'  => 'https://podlibre.social/@Castopod',
                'rel'   => 'me',
                'label' => 'Mastodon',
            ],
            [
                'type'  => 'link',
                'href'  => 'https://bsky.app/profile/castopod.org',
                'label' => 'Bluesky',
            ],
            [
                'type'  => 'link',
                'href'  => 'https://castopod.org/discord',
                'label' => 'Discord',
            ],
            [
                'type'  => 'link',
                'href'  => 'https://opencollective.com/castopod',
                'label' => 'Open collective',
            ],
        ],
    ],
    [
        'title' => lang('Common.footer.aboutAdAures'),
        'items' => [
            [
                'type'  => 'link',
                'href'  => 'https://adaures.com',
                'label' => lang('Common.footer.website'),
            ],
            [
                'type'  => 'link',
                'href'  => 'https://bsky.app/profile/adaures.com',
                'label' => 'Bluesky',
            ],
            [
                'type'  => 'link',
                'href'  => 'https://linkedin.com/company/ad-aures',
                'label' => 'LinkedIn',
            ],
        ],
    ],
    [
        'title' => lang('Common.footer.legal'),
        'items' => [
            [
                'type'  => 'link',
                'href'  => route_to('legal'),
                'label' => lang('Common.footer.legalNotice'),
            ],
            [
                'type'  => 'notice',
                'label' => '🍪 ' . lang('Common.footer.cookieNotice'), // @phpstan-ignore binaryOp.invalid
            ],
        ],
    ],
];
?>

<footer class="bg-brand-800 py-12 border-t border-brand-950 text-white">
    <div class="flex lg:flex-row-reverse flex-col justify-between gap-8 container">
        <div class="gap-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 px-4 w-full max-w-3xl sm:text-left text-center">
            <?php foreach ($sections as $section): ?>
                <section class="flex flex-col gap-y-2">
                    <p class="font-display font-bold text-sm uppercase tracking-wider">
                        <?= $section['title'] ?>
                    </p>
                    <nav class="flex flex-col gap-y-2">
                        <?php foreach ($section['items'] as $item): ?>
                            <?php if ($item['type'] === 'link'): ?>
                                <a class="decoration-2 decoration-pine-500 hover:underline" href="<?= $item['href'] ?>" <?= isset($item['rel']) ? 'rel="' . htmlspecialchars(
                                    $item['rel'],
                                ) . '"' : '' ?>>
                                    <?= $item['label'] ?>
                                </a>
                            <?php else: ?>
                                <small><?= $item['label'] ?></small>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </nav>
                </section>
            <?php endforeach; ?>
        </div>
        <div class="flex flex-col items-start self-center mx-auto lg:mx-0 max-w-sm">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 104 20" fill="currentColor" class="h-8"><path d="M38.56 6.39v9.18h-2.44l-.27-.65a4.35 4.35 0 01-2.77 1A4.68 4.68 0 0128.26 11a4.66 4.66 0 014.82-4.86 4.37 4.37 0 012.8 1l.33-.71zm-3 4.6a2.06 2.06 0 10-4.11 0 2.06 2.06 0 104.11 0zM50.25 3.09v12.49h-2.34l-.3-.68a4.22 4.22 0 01-2.77 1A4.68 4.68 0 0140 11a4.68 4.68 0 014.84-4.9 4.25 4.25 0 012.28.64V3.09zM47.32 11a2.06 2.06 0 10-4.11 0 2.06 2.06 0 104.11 0zM65.69 6.39v9.18h-2.44l-.25-.65a4.33 4.33 0 01-2.77 1A4.67 4.67 0 0155.4 11a4.65 4.65 0 014.81-4.86 4.35 4.35 0 012.8 1l.33-.71zm-3 4.6a2.06 2.06 0 10-4.11 0 2.06 2.06 0 104.11 0zM67.57 11.64V6.4h3.16v5.1c0 1 .52 1.54 1.37 1.54s1.35-.58 1.35-1.54V6.4h3.16v5.24c0 2.57-1.8 4.24-4.51 4.24s-4.53-1.67-4.53-4.24zM84.46 6.36v2.89h-1.23c-1.12 0-1.59.49-1.59 1.66v4.67h-3.16V6.4h2.12l.49 1a3.07 3.07 0 012.53-1.06zM95 10.91a5.9 5.9 0 01-.06.83h-6.86a1.79 1.79 0 001.92 1.6 1.85 1.85 0 001.66-.86h3.14A5 5 0 0185 11a5 5 0 0110-.08zM88.14 10h3.71A1.84 1.84 0 0090 8.58 1.77 1.77 0 0088.14 10zM99.94 15.88c-2.5 0-4-1.32-4-3.31h3a.94.94 0 001.07 1c.46 0 .9-.23.9-.71s-.69-.68-1.57-.82C97.9 11.79 96 11.36 96 9.13c0-1.84 1.57-3 3.92-3s3.86 1.25 3.89 3.11h-3c0-.59-.39-.86-1-.86s-.85.25-.85.67.7.65 1.57.8c1.45.26 3.38.56 3.38 2.89 0 1.92-1.62 3.17-4.06 3.17zM0 0v20h20V0zm13.34 12.66a3.84 3.84 0 01-2.51 3.6 3.9 3.9 0 01-1.33.24 3.84 3.84 0 01-1.33-7.44 1 1 0 011.32.61A1 1 0 018.88 11a1.78 1.78 0 001.24 3.34 1.76 1.76 0 001.16-1.67V7.19a1.64 1.64 0 00-3.28 0 1 1 0 11-2 0 3.69 3.69 0 017.38 0z"></path></svg>
            <small><?= lang('Common.footer.copyright', [
                                                'year' => '2025',
                                            ]) ?></small>
        </div>
    </div>
</footer>
