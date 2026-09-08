<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= lang('Errors.pageNotFound') ?></title>

    <style>
        div.logo {
            height: 200px;
            width: 200px;
            display: inline-block;
            margin-bottom: 1rem;
        }
        .logo svg {
            display: block;
            height: 100%;
            width: 100%;
        }
        body {
            height: 100%;
            background: #f7f9fc;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #777;
            font-weight: 300;
        }
        h1 {
            font-weight: lighter;
            letter-spacing: normal;
            font-size: 3rem;
            margin-top: 0;
            margin-bottom: 0;
            color: #222;
        }
        .wrap {
            max-width: 30rem;
            margin: 5rem auto;
            padding: 2rem;
            text-align: center;
        }
        pre {
            white-space: normal;
            margin-top: 1.5rem;
        }
        code {
            background: #fafafa;
            border: 1px solid #efefef;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            display: block;
        }
        p {
            margin-top: 1.5rem;
        }
        .footer {
            margin-top: 2rem;
            border-top: 1px solid #efefef;
            padding: 1em 2em 0 2em;
            font-size: 85%;
            color: #999;
        }
        a:active,
        a:link,
        a:visited {
            color: #dd4814;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="logo" aria-hidden="true">
            <svg viewBox="0 0 200 200" role="img" aria-label="Page not found" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="error-404-light" x1=".15" y1=".05" x2=".85" y2=".95">
                        <stop offset="0" stop-color="#b9d1f2" />
                        <stop offset="1" stop-color="#8db9ed" />
                    </linearGradient>
                    <linearGradient id="error-404-dark" x1=".15" y1=".05" x2=".85" y2=".95">
                        <stop offset="0" stop-color="#8db9ed" />
                        <stop offset="1" stop-color="#2274e2" />
                    </linearGradient>
                    <filter id="error-404-shadow" x="-40%" y="-40%" width="180%" height="180%">
                        <feDropShadow dx="5" dy="9" stdDeviation="5.5" flood-color="#2274e2" flood-opacity=".26" />
                    </filter>
                </defs>
                <circle cx="100" cy="94" r="72" fill="#2274e2" fill-opacity=".06" />
                <circle cx="100" cy="94" r="55" fill="#2274e2" fill-opacity=".1" />
                <g filter="url(#error-404-shadow)">
                    <rect x="46" y="58" width="108" height="84" rx="16" fill="url(#error-404-light)" />
                    <path d="M46 74a16 16 0 0 1 16-16h76a16 16 0 0 1 16 16v6H46z" fill="url(#error-404-dark)" />
                    <circle cx="60" cy="69" r="3" fill="#fff" fill-opacity=".5" />
                    <circle cx="71" cy="69" r="3" fill="#fff" fill-opacity=".5" />
                    <circle cx="82" cy="69" r="3" fill="#fff" fill-opacity=".5" />
                    <text x="100" y="123" text-anchor="middle" font-size="36" font-weight="800" fill="#2274e2" fill-opacity=".75">404</text>
                </g>
            </svg>
        </div>

        <p>
            <?php if (ENVIRONMENT !== 'production') : ?>
                <?= nl2br(esc($message)) ?>
            <?php else : ?>
                <?= lang('Errors.sorryCannotFind') ?>
            <?php endif; ?>
        </p>
    </div>
</body>
</html>
