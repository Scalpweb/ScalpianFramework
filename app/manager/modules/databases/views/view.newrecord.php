<?php $this->insertBlock('breadcrumb', array('routes' => array('/Manager/Databases' => 'Databases', '/Manager/Databases/View/'.$this->db->getName() => ucfirst($this->db->getName()), '/Manager/Databases/Records/'.$this->db->getName().'/'.$this->table->getName() => 'Records', '/Manager/Databases/NewRecord/'.$this->db->getName().'/'.$this->table->getName() => 'Add a record' ) ) ); ?>

<!-- Page header -->
<div class="page-header">
    <div class="page-title">
        <h5>Table <?php echo $this->table->getName(); ?></h5>
        <span>Add a record</span>
    </div>
</div>
<!-- /page header -->

<?php echo $this->form->getHtml(); ?>