<?php
require_once 'config/path_helper.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>CSS Test</title>
    <link rel="stylesheet" href="<?php echo PathHelper::getCssPath(); ?>styles.css">
</head>
<body>
    <div style="padding: 20px;">
        <h1>CSS Path Test</h1>
        <p>CSS Path: <?php echo PathHelper::getCssPath(); ?></p>
        <p>If you see styled content below, CSS is loading:</p>
        
        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <span class="card-title">Test Card</span>
                <span class="card-icon">✅</span>
            </div>
            <div class="card-value">123</div>
            <div class="card-footer">Test Footer</div>
        </div>
        
        <button class="btn-view" style="margin-top: 20px;">Test Button</button>
    </div>
</body>
</html>