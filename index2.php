<?php
session_start();
include 'header.php';
?>
<style>
    .iframe-container {
        overflow: hidden;
        padding-top: 200%;
        position: relative;
        height: 100%;
    }

    .iframe-container iframe {
        border: 0;
        height: 100%;
        left: 0;
        position: absolute;
        top: 0;
        width: 100%;
    }

    /* 4x3 Aspect Ratio */
    .iframe-container-4x3 {
        xpadding-top: 75%;
    }
</style>
<html>

<body class="dnp-bg-primary">
    <div class=" p-0" style="
    max-width:400px">

        <div class="iframe-container">
            <iframe src="in.php" title="Chat">
        </div>

    </div>
</body>

<?php include 'footer.php'; ?>

</html>