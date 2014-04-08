<html>
    <head>
        <title><?php echo $this->insertAnchor("title", "Orion Framework Test"); ?></title>
        <?php
            AssetsHelper::insertCss();
            AssetsHelper::insertJs();
        ?>
    </head>
    <body>
        <?php $this->insertView(); ?>
        <?php $this->insertBlock('footer'); ?>
    </body>
</html>