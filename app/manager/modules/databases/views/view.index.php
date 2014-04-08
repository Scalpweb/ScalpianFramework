<?php $this->insertBlock('breadcrumb', array('routes' => array('/Manager/Databases' => 'Databases'))); ?>

<!-- Page header -->
<div class="page-header">
    <div class="page-title">
        <h5>Databases</h5>
        <span>Overview</span>
    </div>
</div>
<!-- /page header -->

<h5 class="widget-name"><i class="icon-th"></i>Databases</h5>

<!-- Action tabs -->
<div class="actions-wrapper">
    <div class="actions" style="padding-bottom: 0px;">

        <div id="user-stats">
            <ul class="round-buttons">
                <li><div class="depth"><a href="/Manager/Databases/New" title="Add new database" class="tip"><i class="icon-plus"></i></a></div></li>
            </ul>
        </div>

    </div>
</div>
<!-- /action tabs -->

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
                <th>Info</th>
                <th class="actions-column">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($this->databases as $db): $db = Database::getDatabase($db); ?>
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
                            <li><a href="/Manager/YesNo/Delete-this-database/Manager/Databases/Delete/<?php echo $db->getName(); ?>" class="tip" title="Delete"><i class="icon-trash"></i></a></li>
                        </ul>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- /databases datatable -->

