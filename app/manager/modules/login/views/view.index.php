
<form class="form-horizontal" method="post" action="/Manager/Login">
    <fieldset>
        <div class="row-fluid">
            <div class="span4 offset4">
                <div class="navbar">
                    <div class="navbar-inner">
                        <h6>You must login to access this page</h6>
                    </div>
                </div>
                <div class="well">
                    <?php if(isset($this->error)): ?>
                        <div class="alert margin">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <?php echo $this->error; ?>
                        </div>
                    <?php endif; ?>
                    <?php if(user::getInstance()->isRegistered()): ?>
                        <div class="alert margin">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            You don't have the right credentials to access this page
                        </div>
                    <?php endif; ?>
                    <div class="control-group">
                        <label class="control-label">Username:</label>
                        <div class="controls"><input type="text" name="username" class="span12" /></div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Password:</label>
                        <div class="controls"><input type="password" name="password" class="span12" /></div>
                    </div>
                    <div class="form-actions align-right">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </fieldset>
</form>