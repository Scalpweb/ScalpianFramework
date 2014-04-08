<!-- Breadcrumbs line -->
<?php $title = ''; ?>
<div class="crumbs">
    <ul id="breadcrumbs" class="breadcrumb">
        <li><a href="/Manager">Manager</a></li>
        <?php foreach($this->routes as $key=>$value): ?>
        <li<?php if(end($this->routes) === $value): ?> class="active"<?php endif; ?>><a href="<?php echo $key; ?>" title="<?php echo $value; ?>"><?php echo $value; ?></a></li>
        <?php $title .= $value.' - '; ?>
        <?php endforeach; ?>
    </ul>
</div>
<?php $this->getModule()->setAnchor("title", $title.' Orion Manager'); ?>
<!-- /breadcrumbs line -->