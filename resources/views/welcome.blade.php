<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'HRM API') }}</title>

    <style>
        :root {
            --bg: #f6efe5;
            --panel: rgba(255, 255, 255, 0.78);
            --text: #1f2937;
            --muted: #6b7280;
            --accent: #c2410c;
            --accent-dark: #9a3412;
            --line: rgba(148, 163, 184, 0.25);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: Georgia, "Times New Roman", serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(251, 191, 36, 0.28), transparent 30%),
                radial-gradient(circle at bottom right, rgba(194, 65, 12, 0.24), transparent 32%),
                linear-gradient(135deg, #fffaf3 0%, var(--bg) 100%);
        }

        .card {
            width: min(760px, 100%);
            padding: 48px 40px;
            border: 1px solid var(--line);
            border-radius: 28px;
            background: var(--panel);
            backdrop-filter: blur(12px);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
        }

        .badge {
            display: inline-block;
            margin-bottom: 18px;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent-dark);
            background: rgba(251, 146, 60, 0.14);
        }

        h1 {
            margin: 0;
            font-size: clamp(36px, 6vw, 64px);
            line-height: 1.05;
        }

        p {
            margin: 18px 0 0;
            max-width: 620px;
            font-size: 18px;
            line-height: 1.7;
            color: var(--muted);
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 28px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 20px;
            border-radius: 14px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .button-secondary {
            color: var(--text);
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.72);
        }

        .button-secondary:hover {
            transform: translateY(-1px);
            border-color: rgba(194, 65, 12, 0.3);
        }

        .meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-top: 34px;
        }

        .meta-item {
            padding: 18px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.55);
        }

        .meta-label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .meta-value {
            font-size: 18px;
            font-weight: 700;
        }

        @media (max-width: 640px) {
            .card {
                padding: 32px 24px;
            }

            p {
                font-size: 16px;
            }

            .button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="card">
        <span class="badge">Welcome</span>
        <h1>Chào mừng đến {{ config('app.name', 'HRM API') }}</h1>
        <p>
            Hệ thống API quản lý nhân sự được thiết kế để hỗ trợ các doanh nghiệp trong việc quản lý thông tin nhân viên, chức vụ, phòng ban và các tài nguyên liên quan. API cung cấp các điểm cuối RESTful cho phép truy xuất, tạo, cập nhật và xóa dữ liệu một cách dễ dàng và hiệu quả.
        </p>

        <div class="actions">
            <a class="button button-secondary" href="{{ route('users') }}">Đi đến thêm người dùng</a>
        </div>

        <section class="meta">
            <div class="meta-item">
                <span class="meta-label">Ứng dụng</span>
                <span class="meta-value">{{ config('app.name', 'HRM API') }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Môi trường</span>
                <span class="meta-value">{{ app()->environment() }}</span>
            </div>
        </section>
    </main>
</body>
</html>
