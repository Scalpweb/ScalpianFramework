<?php $this->insertBlock('breadcrumb', array('routes' => array('/Manager/Home' => 'Home'))); ?>

<!-- Page header -->
<div class="page-header">
    <div class="page-title">
        <h5>Home</h5>
        <span>Orion Manager Version 1.0</span>
    </div>
</div>
<!-- /page header -->

<!-- Action tabs -->
<div class="actions-wrapper">
    <div class="actions" style="padding-bottom: 0px;">

        <div id="user-stats">
            <ul class="round-buttons">
                <li><div class="depth"><a href="/Manager/Configuration" title="Configuration" class="tip"><i class="icon-edit"></i></a></div></li>
                <li><div class="depth"><a href="/Manager/Applications" title="Applications" class="tip"><i class="icon-tasks"></i></a></div></li>
                <li><div class="depth"><a href="/Manager/Databases" title="Databases" class="tip"><i class="icon-table"></i></a></div></li>
                <li><div class="depth"><a href="/Manager/Routing" title="Routing" class="tip"><i class="icon-random"></i></a></div></li>
                <li><div class="depth"><a href="/Manager/Crons" title="Crons" class="tip"><i class="icon-cogs"></i></a></div></li>
                <li><div class="depth"><a href="/Manager/Plugins" title="Plugins" class="tip"><i class="icon-bolt"></i></a></div></li>
            </ul>
        </div>

    </div>
</div>
<!-- /action tabs -->

<h5 class="widget-name"><i class="icon-th"></i>Applications</h5>

<!-- With titles -->
<div class="media row-fluid">

    <?php foreach(CacheHelper::get('Manager/Apps', 'Manager::getApplications', array(), CachePeriod::ONE_DAY) as $app): if($app->getName() != $this->getApplication()->getName()): ?>
        <div class="span3">
            <div class="widget">
                <div class="well">
                    <div class="item-info">
                        <a href="/Manager/Applications/Edit/<?php echo $app->getName(); ?>" title="" class="item-title">
                            <?php echo ucfirst($app->getName()); ?>
                            <span class="label label-inverse pull-right"><?php echo ucfirst($app->getStatus()); ?></span>
                        </a>
                        <p class="item-buttons">
                            <a href="/Manager/Applications/Edit/<?php echo $app->getName(); ?>" class="btn btn-info tip" title="Edit"><i class="icon-pencil"></i></a>
                            <?php if($app->getStatus() == ApplicationStatus::APP_ON_HOLD): ?>
                                <a href="/Manager/Applications/Status/<?php echo $app->getName(); ?>/Play" class="btn btn-danger tip" title="Put on hold"><i class="icon-play"></i></a>
                            <?php else: ?>
                                <a href="/Manager/Applications/Status/<?php echo $app->getName(); ?>/Pause" class="btn btn-danger tip" title="Put on hold"><i class="icon-pause"></i></a>
                            <?php endif;?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; endforeach; ?>

</div>
<!-- /with titles -->


<h5 class="widget-name"><i class="icon-th"></i>Databases</h5>

<!-- Databases datatable -->
<div class="widget">
    <div class="navbar">
        <div class="navbar-inner">
            <h6>Databases</h6>
            <div class="nav pull-right">
                <a href="#" class="dropdown-toggle navbar-icon" data-toggle="dropdown"><i class="icon-cog"></i></a>
                <ul class="dropdown-menu pull-right">
                    <li><a href="/Manager/Databases/New"><i class="icon-plus"></i>Add new database</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="table-overflow">
        <table class="table table-striped table-bordered table-checks media-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>File info</th>
                <th class="actions-column">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach(CacheHelper::get('Manager/Databases', 'Database::findDatabases', array(), CachePeriod::ONE_DAY) as $db): $db = Database::getDatabase($db); ?>
            <tr>
                <td><i class="fam-database-connect"></i><?php echo $db->getName(); ?></td>
                <td class="file-info">
                    <span><strong>Size:</strong> <?php echo OrionTools::humanSize(CacheHelper::get('Manager/DatabaseSize/'.$db->getName(), array($db, 'getSize'), array(), CachePeriod::ONE_DAY)); ?></span>
                    <span><strong>Tables:</strong> <?php echo sizeof($db->getTables()); ?></span>
                    <span><strong>Host:</strong> <?php echo $db->getHost(); ?></span>
                </td>
                <td>
                    <ul class="navbar-icons">
                        <li><a href="/Manager/Databases/NewTable/<?php echo $db->getName() ?>" class="tip" title="Add new table"><i class="icon-plus"></i></a></li>
                        <li><a href="/Manager/Databases/View/<?php echo $db->getName() ?>" class="tip" title="View tables"><i class="icon-reorder"></i></a></li>
                        <li><a href="/Manager/Databases/Edit/<?php echo $db->getName() ?>" class="tip" title="Edit parameters"><i class="icon-cogs"></i></a></li>
                    </ul>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- /databases datatable -->

<br />
<h5 class="widget-name"><i class="icon-th"></i>Routing</h5>

<!-- Routing datatable -->
<div class="widget">
    <div class="navbar">
        <div class="navbar-inner">
            <h6>Databases</h6>
            <div class="nav pull-right">
                <a href="#" class="dropdown-toggle navbar-icon" data-toggle="dropdown"><i class="icon-cog"></i></a>
                <ul class="dropdown-menu pull-right">
                    <li><a href="/Manager/Routing/New"><i class="icon-plus"></i>Add new route</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="table-overflow">
        <table class="table table-striped table-bordered table-checks media-table">
            <thead>
            <tr>
                <th>URL</th>
                <th>Target</th>
                <th class="actions-column">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach(Router::getInstance()->getCustomRoutes() as $url => $target): ?>
                <tr>
                    <td style="width: 70%;"><?php echo $url; ?></td>
                    <td>
                        <strong>Application: </strong><?php echo $target['application']; ?><br />
                        <strong>Module: </strong><?php echo $target['module']; ?><br />
                        <strong>Action: </strong><?php echo $target['action']; ?>
                    </td>
                    <td>
                        <ul class="navbar-icons">
                            <li><a href="/Manager/Routing/Edit/<?php echo $url; ?>" class="tip" title="Edit route"><i class="icon-cogs"></i></a></li>
                            <li><a href="/Manager/Routing/Delete/<?php echo $url; ?>" class="tip" title="Delete route"><i class="icon-trash"></i></a></li>
                        </ul>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- /routing datatable -->
