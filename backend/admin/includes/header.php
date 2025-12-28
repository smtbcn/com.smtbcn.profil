<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Panel'; ?></title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0a0e27;
            color: #e4e4e7;
            padding-bottom: 80px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        h3 {
            font-size: 18px;
            margin-bottom: 16px;
        }

        .card {
            background: #151932;
            border: 1px solid #2a2f4a;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            color: #94a3b8;
            margin-bottom: 8px;
        }

        input,
        select,
        textarea {
            width: 100%;
            background: #1a1f3a;
            border: 1px solid #2a2f4a;
            border-radius: 8px;
            padding: 12px;
            color: #e4e4e7;
            font-size: 15px;
            margin-bottom: 16px;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid #10b981;
            color: #10b981;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #151932;
            border-top: 1px solid #2a2f4a;
            padding: 12px;
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .nav-item {
            flex: 1;
            max-width: 120px;
            padding: 12px;
            text-align: center;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 8px;
        }

        .nav-item.active {
            color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
        }

        .nav-item i {
            display: block;
            font-size: 20px;
            margin-bottom: 4px;
        }

        .nav-item span {
            display: block;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="container">