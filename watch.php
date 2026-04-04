<?php
include 'header.php';

if ($plugin_watch !== '1') {
    echo "<h2>Plugin Disabled</h2><p>The 'Watch' feature is currently disabled by the administrator.</p>";
    include 'footer.php';
    exit;
}
?>

<div class="watch-container">
    <h2>Watch Featured Content</h2>
    <div id="watchContent">
        <!-- Plugin content logic goes here -->
        <p>Welcome to the Watch plugin page! You can stream your favorite shows and movies here.</p>
        <div id="featuredWatch" class="main-grid"></div>
    </div>
</div>

<script src="js/watch.js"></script>
<?php include 'footer.php'; ?>
