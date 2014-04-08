<?php $this->insertBlock('breadcrumb', array('routes' => array($this->url => 'Back'))); ?>

<!-- Page header -->
<div class="page-header">
    <div class="page-title">
        <h5>Confirm</h5>
    </div>
</div>
<!-- /page header -->

<div class="widget">
    <div class="navbar">
        <div class="navbar-inner">
            <h6><?php echo $this->question; ?> ?</h6>
        </div>
    </div>
    <div class="row-fluid well body">

        <div class="span4"><a href="<?php echo $this->url; ?>" class="btn btn-success btn-block confirm">Yes</a></div>
        <div class="span4"><a href="<?php echo History::get(1); ?>" class="btn btn-danger btn-block bs-alert">No</a></div>

    </div>
</div>