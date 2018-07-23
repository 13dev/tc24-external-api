<?php
// Register the modules dependencies, routes etc ...
// Iteract All Modules
foreach (glob(__DIR__ .'/../src/Modules/*' , GLOB_ONLYDIR) as $module) {
    // Get files of modules
    foreach (glob($module . '/*.php') as $filename) {

        die();
    }
}