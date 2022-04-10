<?php

use yii\helpers\Html;

?>
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>
    
    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <?php Html::a('<i class="fas fa-sign-out-alt"></i>', ['/site/logout'], ['data-method' => 'post', 'class' => 'nav-link']) ?>
        </li>
        <li class="nav-item dropdown user-menu">
<!--            
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <img src="<?php Yii::getAlias("@web")?>/img/no-profile.jpg" class="user-image img-circle elevation-2" alt="User Image">
                <span class="d-none d-md-inline">Usuario Testing</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                 User image 
                <li class="user-header bg-primary">
                    <img src="<?php Yii::getAlias("@web")?>/img/no-profile.jpg" class="img-circle elevation-2" alt="User Image">

                    <p>
                        Usuario Testing
                    </p>
                </li>
                 Menu Body 
                <li class="user-body">
                </li>
                 Menu Footer
                <li class="user-footer">
                    <a href="#" class="btn btn-default btn-flat">Profile</a>
                    <?php Html::a('Sign out', ['/site/logout'], ['data-method' => 'post', 'class' => 'btn btn-default btn-flat float-right']) ?>
                </li>
            </ul>
            -->
        </li>
    </ul>
</nav>