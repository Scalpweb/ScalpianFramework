<form class="form-horizontal" method="<?php echo $this->getMethod(); ?>" action="<?php echo $this->getAction(); ?>"">
    <?php echo $this->tokenFieldTag(); ?>
    <fieldset>
                <?php if(sizeof($this->getErrors()) > 0): ?>
                    <div class="alert margin">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <?php foreach($this->getErrors() as $err): echo '<i class="fam-cross"></i>'.$this->formatError($err).'<br />'; endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php echo OrionTools::linef($this->tokenFieldTag(), 1); ?>
                <?php foreach($this->getFields() as $name => $field):?>
                    <div class="control-group">
                        <label class="control-label"><?php echo $this->getText($name); ?></label>
                        <div class="controls"><?php echo $field->getHtml(array('class' => 'span12')); ?><?php echo $field->getLegend() != '' ? '<br />'.$field->getLegend() : ''; ?></div>
                    </div>
                <?php endforeach; ?>
                <div class="form-actions align-right">
                    <input type="submit" value="Submit" class="btn btn-primary" />
                </div>
    </fieldset>
</form>