<?php $this->insertBlock('breadcrumb', array('routes' => array('/Manager/Databases' => 'Databases', '/Manager/Databases/View/'.$this->db->getName() => ucfirst($this->db->getName()) ) ) ); ?>

<!-- Page header -->
<div class="page-header">
    <div class="page-title">
        <h5>Database <?php echo $this->db->getName(); ?></h5>
        <span>Tables overview</span>
    </div>
</div>
<!-- /page header -->

<h5 class="widget-name"><i class="icon-th"></i><?php echo ucfirst($this->db->getName()); ?> tables overview</h5>

<!-- Action tabs -->
<div class="actions-wrapper">
    <div class="actions" style="padding-bottom: 0px;">

        <div id="user-stats">
            <ul class="round-buttons">
                <li><div class="depth"><a href="/Manager/Databases/NewTable/<?php echo $this->db->getName(); ?>" title="Add new table" class="tip"><i class="icon-plus"></i></a></div></li>
                <li><div class="depth"><a href="/Manager/Databases/Edit/<?php echo $this->db->getName(); ?>" title="Edit database parameters" class="tip"><i class="icon-cogs"></i></a></div></li>
            </ul>
        </div>

    </div>
</div>
<!-- /action tabs -->

<!-- Databases datatable -->
<div class="widget">
    <div class="navbar">
        <div class="navbar-inner">
            <h6>Tables</h6>
            <div class="nav pull-right">
                <a href="#" class="dropdown-toggle navbar-icon" data-toggle="dropdown"><i class="icon-cog"></i></a>
                <ul class="dropdown-menu pull-right">
                    <li><a href="/Manager/Databases/NewTable/<?php echo $this->db->getName(); ?>"><i class="icon-plus"></i>Add new table</a></li>
                    <li><a href="/Manager/Databases/Edit/<?php echo $this->db->getName(); ?>"><i class="icon-cogs"></i>Edit database parameters</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="table-overflow">
        <table class="table table-striped table-bordered table-checks media-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Rows</th>
                <th>Size</th>
                <th class="actions-column">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($this->tables as $table): ?>
                <tr>
                    <td style="width: 50%;"><i class="fam-database-table"></i><?php echo $table->getName(); ?></td>
                    <td><?php echo $table->count(); ?></td>
                    <td><?php echo OrionTools::humanSize($table->getSize()); ?></td>
                    <td>
                        <ul class="navbar-icons">
                            <li><a href="/Manager/Databases/Records/<?php echo $this->db->getName() ?>/<?php echo $table->getName() ?>" class="tip" title="View Records"><i class="icon-reorder"></i></a></li>
                            <li><a href="/Manager/Databases/EditTable/<?php echo $this->db->getName() ?>/<?php echo $table->getName() ?>" class="tip" title="Edit table parameters"><i class="icon-cogs"></i></a></li>
                            <li><a href="/Manager/YesNo/Delete-this-table/Manager/Databases/DeleteTable/<?php echo $this->db->getName() ?>/<?php echo $table->getName() ?>" class="tip" title="Delete table"><i class="icon-trash"></i></a></li>
                        </ul>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- /databases datatable -->
