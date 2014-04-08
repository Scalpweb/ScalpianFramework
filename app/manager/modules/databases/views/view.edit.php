<?php $this->insertBlock('breadcrumb', array('routes' => array('/Manager/Databases' => 'Databases', '/Manager/Databases/View/'.$this->db->getName() => ucfirst($this->db->getName()), '/Manager/Databases/Edit/'.$this->db->getName() => 'Edit' ) ) ); ?>

<!-- Page header -->
<div class="page-header">
    <div class="page-title">
        <h5>Database <?php echo $this->db->getName(); ?></h5>
        <span>Edit parameters</span>
    </div>
</div>
<!-- /page header -->

<?php echo $this->form->getHtml(); ?>