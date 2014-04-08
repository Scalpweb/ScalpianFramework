<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title><?php echo $this->insertAnchor("title", "Orion Manager"); ?></title>
    <link href="/css/manager/bootstrap.css" rel="stylesheet" type="text/css" />
    <!--[if IE 8]><link href="/css/ie8.css" rel="stylesheet" type="text/css" /><![endif]-->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/js/manager/excanvas.min.js"></script>
    <script type="text/javascript" src="/js/manager/jquery.flot.js"></script>
    <script type="text/javascript" src="/js/manager/jquery.flot.resize.js"></script>
    <script type="text/javascript" src="/js/manager/jquery.sparkline.min.js"></script>
    <script type="text/javascript" src="/js/manager/jquery.easytabs.min.js"></script>
    <script type="text/javascript" src="/js/manager/jquery.collapsible.min.js"></script>
    <script type="text/javascript" src="/js/manager/prettify.js"></script>
    <script type="text/javascript" src="/js/manager/jquery.colorpicker.js"></script>
    <script type="text/javascript" src="/js/manager/jquery.timepicker.min.js"></script>
    <script type="text/javascript" src="/js/manager/jquery.fancybox.js"></script>
    <script type="text/javascript" src="/js/manager/jquery.fullcalendar.min.js"></script>
    <script type="text/javascript" src="/js/manager/jquery.uniform.min.js"></script>
    <script type="text/javascript" src="/js/manager/jquery.tagsinput.min.js"></script>
    <script type="text/javascript" src="/js/manager/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="/js/manager/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/manager/index.js"></script>
    <script type="text/javascript" src="/js/manager/graph.js"></script>
    <script type="text/javascript" src="/js/manager/chart1.js"></script>
    <script type="text/javascript" src="/js/manager/chart2.js"></script>
    <script type="text/javascript" src="/js/manager/chart3.js"></script>
    <?php AssetsHelper::insertCss(); ?>
    <?php AssetsHelper::insertJs(); ?>
</head>
<body>

<!-- Fixed top -->
<div id="top">
    <div class="fixed">
        <ul class="top-menu">
            <li class="dropdown">
                <a class="user-menu" data-toggle="dropdown"><span>Quick Access <b class="caret"></b></span></a>
                <ul class="dropdown-menu">
                    <li><a href="/Manager/Applications" title=""><i class="icon-tasks"></i>Applications</a></li>
                    <li><a href="/Manager/Databases" title=""><i class="icon-table"></i>Database</a></li>
                    <li><a href="/Manager/Databases/Fixtures" title=""><i class="icon-list-ul"></i>Fixtures</a></li>
                    <li><a href="/Manager/Routing" title=""><i class="icon-random"></i>Custom routing</a></li>
                    <li><a href="/Manager/Login/Logout" title=""><i class="icon-share-alt"></i>Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>
</div>
<!-- /fixed top -->


<!-- Content container -->
<div id="container">

<!-- Sidebar -->
<div id="sidebar">

    <div class="sidebar-tabs">

        <div id="general">

            <!-- Sidebar user -->
            <div class="sidebar-user widget">
                <div class="navbar"><div class="navbar-inner"><h6>Orion Manager</h6></div></div>
            </div>
            <!-- /sidebar user -->

            <!-- Main navigation -->
            <ul class="navigation widget">
                <li<?php if($this->getName() == 'Home'): ?> class="active"<?php endif; ?>><a href="/Manager"><i class="icon-home"></i>Dashboard</a></li>
                <li<?php if($this->getName() == 'Configuration'): ?> class="active"<?php endif; ?>><a href="/Manager/Configuration"><i class="icon-edit"></i>Configuration</a></li>
                <li<?php if($this->getName() == 'Applications'): ?> class="active"<?php endif; ?>><a href="/Manager/Applications"><i class="icon-tasks"></i>Applications</a></li>
                <li<?php if($this->getName() == 'Databases'): ?> class="active"<?php endif; ?>><a href="/Manager/Databases"><i class="icon-table"></i>Databases</a></li>
                <li<?php if($this->getName() == 'Routing'): ?> class="active"<?php endif; ?>><a href="/Manager/Routing"><i class="icon-random"></i>Routing</a></li>
                <li<?php if($this->getName() == 'Crons'): ?> class="active"<?php endif; ?>><a href="/Manager/Crons"><i class="icon-cogs"></i>Crons</a></li>
                <li<?php if($this->getName() == 'Plugins'): ?> class="active"<?php endif; ?>><a href="/Manager/Plugins"><i class="icon-bolt"></i>Plugins</a></li>
            </ul>
            <!-- /main navigation -->

            <div class="general-stats widget">
                <ul class="head">
                    <li><span>Apps</span></li>
                    <li><span>Modules</span></li>
                    <li><span>Actions</span></li>
                </ul>
                <ul class="body">
                    <li><strong><?php echo sizeof(CacheHelper::get('Manager/Apps', 'Manager::getApplications', array(), CachePeriod::ONE_DAY)); ?></strong></li>
                    <li><strong><?php echo sizeof(CacheHelper::get('Manager/Modules', 'Manager::getModules', array(), CachePeriod::ONE_DAY)); ?></strong></li>
                    <li><strong><?php echo sizeof(CacheHelper::get('Manager/Actions', 'Manager::getActions', array(), CachePeriod::ONE_DAY)); ?></strong></li>
                </ul>
            </div>

            <div class="general-stats widget">
                <ul class="head">
                    <li><span>Databases</span></li>
                    <li><span>Tables</span></li>
                    <li><span>Records</span></li>
                </ul>
                <ul class="body">
                    <li><strong><?php echo sizeof(CacheHelper::get('Manager/Databases', 'Database::findDatabases', array(), CachePeriod::ONE_DAY)); ?></strong></li>
                    <li><strong><?php echo sizeof(CacheHelper::get('Manager/Tables', 'Manager::getTables', array(), CachePeriod::ONE_DAY)); ?></strong></li>
                    <li><strong><?php echo CacheHelper::get('Manager/RowCount', 'Manager::getRowCount', array(), CachePeriod::ONE_DAY); ?></strong></li>
                </ul>
            </div>

            <div class="general-stats widget">
                <ul class="head">
                    <li><span>Routes</span></li>
                    <li><span>Config.</span></li>
                    <li><span>Crons</span></li>
                </ul>
                <ul class="body">
                    <li><strong><?php echo sizeof(Orion::getRouter()->getCustomRoutes()); ?></strong></li>
                    <li><strong><?php echo sizeof(Orion::getConfiguration()->getAll()); ?></strong></li>
                    <li><strong><?php echo CronManager::count(); ?></strong></li>
                </ul>
            </div>

        </div>

    </div>
</div>
<!-- /sidebar -->


<!-- Content -->
<div id="content">

<!-- Content wrapper -->
<div class="wrapper">

    <?php if(Manager::isUsingDefaultConfiguration()): ?>
    <div class="alert alert-error" style="margin-top: 16px;">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>Warning!</strong> Your manager is actually configured with default user. Please change your configuration.
    </div>
    <?php endif; ?>

    <?php foreach(User::getInstance()->getFlashes() as $flash): ?>
        <div class="alert alert-<?php echo strtolower($flash['type']); ?>" style="margin-top: 16px;">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <?php echo $flash['message']; ?>
        </div>
    <?php endforeach; User::getInstance()->clearFlashes(); ?>

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
