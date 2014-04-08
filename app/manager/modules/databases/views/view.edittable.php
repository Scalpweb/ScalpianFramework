<?php $this->insertBlock('breadcrumb', array('routes' => array('/Manager/Databases' => 'Databases', '/Manager/Databases/View/'.$this->db->getName() => ucfirst($this->db->getName()), '/Manager/Databases/EditTable/'.$this->db->getName().'/'.$this->table->getName() => 'Edit Table' ) ) ); ?>

<!-- Page header -->
<div class="page-header">
    <div class="page-title">
        <h5>Table <?php echo $this->table->getName(); ?></h5>
        <span>Edit parameters</span>
    </div>
</div>
<!-- /page header -->

<h5 class="widget-name"><i class="icon-columns"></i>Columns</h5>
<div class="row-fluid">

    <div class="widget">
        <div class="tabbable"><!-- default tabs -->
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab1" data-toggle="tab"><i class="icon-list"></i>Structure</a></li>
                <li class=""><a href="#tab2" data-toggle="tab"><i class="icon-plus"></i>Add column</a></li>
                <li class=""><a href="#tab4" data-toggle="tab"><i class="icon-plus"></i>Add relation</a></li>
                <li class=""><a href="#tab3" data-toggle="tab"><i class="icon-plus"></i>Add index</a></li>
                <li class=""><a href="#tab5" data-toggle="tab"><i class="icon-cogs"></i>Edit table</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab1">

                    <h5>Columns</h5>

                    <div class="table-overflow">
                        <table class="table table-striped table-bordered table-checks media-table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>AI</th>
                                <th>Details</th>
                                <th class="actions-column">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $i = 0; foreach($this->table->getRows() as $row): $i++; ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $row->getName(); ?></td>
                                    <td><?php echo $row->getType(); ?></td>
                                    <td><?php echo $row->getAutoIncrement() ? 'True' : 'False'; ?></td>
                                    <td class="file-info"></td>
                                    <td>
                                        <?php if($row->getName() !== 'id'): ?>
                                        <ul class="navbar-icons">
                                            <li><a href="/Manager/Databases/EditColumn/<?php echo $this->db->getName().'/'.$this->table->getName().'/'.$row->getName(); ?>" class="tip" title="Edit"><i class="icon-cogs"></i></a></li>
                                            <li><a href="/Manager/YesNo/Delete-this-column/Manager/Databases/DeleteColumn/<?php echo $this->db->getName().'/'.$this->table->getName().'/'.$row->getName(); ?>" class="tip" title="Delete"><i class="icon-trash"></i></a></li>
                                        </ul>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <br />

                    <h5>Relations</h5>

                    <div class="table-overflow">
                        <table class="table table-striped table-bordered table-checks media-table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Target</th>
                                <th class="actions-column">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $i = 0; foreach($this->table->getRelations() as $rel): $i++; ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $rel->getAlias(); ?></td>
                                    <td><?php echo $rel->getType(); ?></td>
                                    <td><?php echo $rel->getTable2()->getName(); ?></td>
                                    <td>
                                        <ul class="navbar-icons">
                                            <li><a href="/Manager/YesNo/Delete-this-relation/Manager/Databases/DeleteRelation/<?php echo $this->db->getName().'/'.$this->table->getName().'/'.$rel->getAlias(); ?>" class="tip" title="Delete"><i class="icon-trash"></i></a></li>
                                        </ul>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <br />

                    <h5>Indexes</h5>

                    <div class="table-overflow">
                        <table class="table table-striped table-bordered table-checks media-table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Field</th>
                                <th class="actions-column">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $i = 0; foreach($this->table->getIndexes() as $index): $i++; ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $index['name']; ?></td>
                                    <td><?php echo $index['type']; ?></td>
                                    <td><?php echo $index['field']; ?></td>
                                    <td>
                                        <ul class="navbar-icons">
                                            <li><a href="/Manager/YesNo/Delete-this-index/Manager/Databases/DeleteIndex/<?php echo $this->db->getName().'/'.$this->table->getName().'/'.$index['name']; ?>" class="tip" title="Delete"><i class="icon-trash"></i></a></li>
                                        </ul>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
                <div class="tab-pane" id="tab2">
                    <?php echo $this->formColumn->getHtml(); ?>
                </div>
                <div class="tab-pane" id="tab3">
                    <?php echo $this->formIndex->getHtml(); ?>
                </div>
                <div class="tab-pane" id="tab4">
                    <?php echo $this->formRelation->getHtml(); ?>
                </div>
                <div class="tab-pane" id="tab5">
                    <?php echo $this->form->getHtml(); ?>
                </div>
            </div>
        </div>
    </div>

</div>