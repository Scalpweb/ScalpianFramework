<?php

/**
 * Warning: this file is auto-generated by the Orion Manager.
 * Do not delete or modify it directly.
 * If you want to add some custom configuration rules, please create another file into the config directory
 */

Orion::getRouter()->addCustomRoutes(
    array(
        'this_is_a_/custom/routing/example/{num}-{any}-{alpha}-{alphanum}' => array('application' => 'Public', 'module' => 'Home', 'action' => 'Custom'),
        'variables/{alphanum}/can/go/{num}-anywhere-{any}'                 => array('application' => 'Public', 'module' => 'Home', 'action' => 'Custom'),
    )
);
Orion::getConfiguration()->set('User/UseDatabase', true);
Orion::getConfiguration()->set('User/UserTable', 'users');
Orion::getConfiguration()->set('User/CredentialsTable', 'usershascredentials');
Orion::getConfiguration()->set('User/CredentialsField', 'name');
Orion::getConfiguration()->set('User/LoginField', 'login');
Orion::getConfiguration()->set('User/PasswordField', 'password');