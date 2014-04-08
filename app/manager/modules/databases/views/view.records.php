<?php $this->insertBlock('breadcrumb', array('routes' => array('/Manager/Databases' => 'Databases', '/Manager/Databases/View/'.$this->db->getName() => ucfirst($this->db->getName()), '/Manager/Databases/Records/'.$this->db->getName().'/'.$this->table->getName() => 'Records' ) ) ); ?>

<!-- Page header -->
<div class="page-header">
    <div class="page-title">
        <h5>Table <?php echo $this->table->getName(); ?></h5>
        <span>Records</span>
    </div>
</div>
<!-- /page header -->

<!-- Action tabs -->
<div class="actions-wrapper">
    <div class="actions" style="padding-bottom: 0px;">
        <div id="user-stats">
            <ul class="round-buttons">
                <li><div class="depth"><a href="/Manager/Databases/NewRecord/<?php echo $this->db->getName() ?>/<?php echo $this->table->getName() ?>" title="Add new record" class="tip"><i class="icon-plus"></i></a></div></li>
            </ul>
        </div>
    </div>
</div>

<h5 class="widget-name"><i class="icon-reorder"></i>Records</h5>
<div class="widget">
    <div class="table-overflow">
        <table class="table table-striped table-bordered table-checks media-table">
            <thead>
            <tr>
                <?php foreach($this->table->getRows() as $row): ?>
                    <th class="actions-column"><?php echo $row->getName(); ?></th>
                <?php endforeach; ?>
                <th class="actions-column">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($this->records as $record): ?>
                <tr>
                    <?php foreach($this->table->getRows() as $row): ?>
                        <td><?php echo $record->__get($row->getName()); ?></td>
                    <?php endforeach; ?>
                    <th class="actions-column">
                        <ul class="navbar-icons">
                            <li><a href="/Manager/Databases/EditRecord/<?php echo $this->db->getName() ?>/<?php echo $this->table->getName() ?>/<?php echo $record->__get('id'); ?>" class="tip" title="Edit"><i class="icon-cogs"></i></a></li>
                            <li><a href="/Manager/YesNo/Delete-this-record/Manager/Databases/DeleteRecord/<?php echo $this->db->getName() ?>/<?php echo $this->table->getName() ?>/<?php echo $record->__get('id'); ?>" class="tip" title="Delete"><i class="icon-trash"></i></a></li>
                        </ul>
                    </th>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
