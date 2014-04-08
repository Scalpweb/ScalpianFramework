<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title>Orion Manager</title>
    <link href="/css/manager/bootstrap.css" rel="stylesheet" type="text/css" />
    <!--[if IE 8]><link href="/css/ie8.css" rel="stylesheet" type="text/css" /><![endif]-->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/js/manager/jquery.uniform.min.js"></script>
    <script type="text/javascript" src="/js/manager/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/manager/login.js"></script>
    <?php AssetsHelper::insertCss(); ?>
    <?php AssetsHelper::insertJs(); ?>
</head>
<body>

<!-- Fixed top -->
<div id="top">
    <div class="fixed">
        <ul class="top-menu">
            <li class="dropdown">
                <a class="user-menu" href="/Manager"><span>Login</span></a>
            </li>
        </ul>
    </div>
</div>
<!-- /fixed top -->

<!-- Content container -->
<div id="container">

<!-- Content -->
<div id="content">

<!-- Content wrapper -->
<div class="wrapper">
    <br /><br />
    <?php $this->insertView(); ?>
</div>
<!-- /content wrapper -->

</div>
<!-- /content -->

</div>
<!-- /content container -->


<!-- Footer -->
<div id="footer">
    <div class="copyrights">&copy;  Orion Framework</div>
    <ul class="footer-links">
        <li><a href="" title=""><i class="icon-hand-right"></i>Website</a></li>
    </ul>
</div>
<!-- /footer -->
<?php  Orion::getLogger()->buildConsole(); ?>
</body>
</html>
