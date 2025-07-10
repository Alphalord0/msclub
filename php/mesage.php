<?php

if(isset($_SESSION['mesage']))
{
    ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Hey!</strong> <?= $_SESSION['mesages']; ?>
            <button type='button' class="close" data-bs-dismiss="alert"></button>
        </div>
    <?php
    unset($_SESSION['mesagse']);
}



?>