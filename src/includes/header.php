<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Course Registration System'; ?></title>
    <!-- CSS modules -->
    <link rel="stylesheet" href="../../assets/css/base.css">
    <?php if (isset($cssFiles) && is_array($cssFiles)): ?>
        <?php foreach ($cssFiles as $cssFile): ?>
            <link rel="stylesheet" href="../../assets/css/<?php echo $cssFile; ?>.css">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <header>
        <div class="container">
            <h1>Course Registration System</h1>
        </div>
    </header>