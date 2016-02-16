<?php
//echo 'hi'; exit;
session_start();
unset($_SESSION['is_logged_in']);
header('Location:/');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

