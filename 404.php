<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sahifa topilmadi - 404</title>
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/base.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: var(--space-6);
        }
        .error-page {
            max-width: 500px;
        }
        .error-code {
            font-size: clamp(4rem, 15vw, 10rem);
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
            margin-bottom: var(--space-4);
        }
        .error-message {
            font-size: var(--text-xl);
            color: var(--text-secondary);
            margin-bottom: var(--space-8);
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-code">404</div>
        <h1>Sahifa topilmadi</h1>
        <p class="error-message">Siz qidirgan sahifa mavjud emas yoki ko'chirilgan.</p>
        <a href="/" class="btn btn-primary">Bosh sahifaga qaytish</a>
    </div>
</body>
</html>
