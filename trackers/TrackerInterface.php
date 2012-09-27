<?php

/**
 * main tracker interface
 */
interface trackerinterface {

    public function dispose();
    
    public function get_applied_patches();
    
    public function get_applied_patch_names();

    public function has_error();

    public function insert_new_version($tracking_item);
}

?>
