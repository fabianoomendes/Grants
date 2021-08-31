<?php

function requireValidSession(){
    $user = null;
    if(isset($_SESSION['user'])){
        $user = $_SESSION['user'];
    }else if(isset($_COOKIE['user'])){
        $user = $_COOKIE['user'];
    }

    if(is_null($user)){
        header("Location: login.php");
    }else{
        $userLogin = User::getOne(['email' => $user->email, 'password' => $user->password]);        
        if(is_null($userLogin)){
            header("Location: login.php");
        }
    }
}

function validSession(){
    $user = null;
    if(isset($_SESSION['user'])){
        $user = $_SESSION['user'];
    }else if(isset($_COOKIE['user'])){
        $user = $_COOKIE['user'];
    }

    if(isset($user) && !is_null($user)){
        $userLogin = User::getOne(['email' => $user->email, 'password' => $user->password]);
        
        if(!is_null($userLogin)){
            header("Location: home.php");
        }
    }
}