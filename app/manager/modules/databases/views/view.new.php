<?php $this->insertBlock('breadcrumb', array('routes' => array('/Manager/Databases' => 'Databases', '/Manager/Databases/New' => 'Add new database'))); ?>

<!-- Page header -->
<div class="page-header">
    <div class="page-title">
        <h5>Databases</h5>
        <span>Add new database</span>
    </div>
</div>
<!-- /page header -->

<?php echo $this->form->getHtml(); ?>