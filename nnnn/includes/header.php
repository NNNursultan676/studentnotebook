<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Student Dark Notebook'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rock+Salt&family=Permanent+Marker&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<?php if (isset($_SESSION['user_id'])): ?>
    <?php include __DIR__ . '/navbar.php'; ?>
<?php endif; ?>
<div class="container">
