<?php $this->insertBlock('breadcrumb', array('routes' => array('/Manager/Databases' => 'Databases', '/Manager/Databases/View/'.$this->db->getName() => ucfirst($this->db->getName()), '/Manager/Databases/EditTable/'.$this->db->getName().'/'.$this->table->getName() => 'Edit Table', '/Manager/Databases/EditColumn/'.$this->db->getName().'/'.$this->table->getName().'/'.$this->table->column => 'Edit Column' ) ) ); ?>

    <!-- Page header -->
    <div class="page-header">
        <div class="page-title">
            <h5>Table <?php echo $this->table->getName(); ?></h5>
            <span>Edit column <?php echo $this->column; ?></span>
        </div>
    </div>
    <!-- /page header -->

<?php echo $this->form->getHtml(); ?>