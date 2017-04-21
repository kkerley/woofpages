<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/*
* Creates a cache file from a text
* 
* @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
* @version 1.0
* @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
* @copyright Copyright (c) 2012, Ernest Marcinko
*/

if (!class_exists('wpd_TextCache')) {
    class wpd_TextCache {

        private $interval;
        private $cache_name;
        private $cache_path;
        private $last_file_mtime = 0;
        private $last_file_path = "";

        public function __construct($cache_path, $cache_name = "txt", $interval = 36000) {
            $this->cache_name = $cache_name;
            $this->cache_path = $cache_path;
            $this->interval = $interval;
        }

        public function file_path($file) {
            return trailingslashit($this->cache_path) . $this->cache_name . "_" . $file . ".wpd";
        }

        public function getCache($file = "") {
            global $wp_filesystem;

            $file = $this->file_path($file);
            $this->last_file_path = $file;

            if ( $wp_filesystem->is_file($file) ) {
                $filetime = $wp_filesystem->mtime($file);
            } else {
                return false;
            }
            if ( (time() - $filetime) > $this->interval )
                return false;
            $this->last_file_mtime = $filetime;
            return $wp_filesystem->get_contents($file);
        }

        public function getLastFileMtime() {
            return $this->last_file_mtime;
        }

        public function setCache($content, $file = "") {
            global $wp_filesystem;

            if ( $file === '' ) {
                $file = $this->last_file_path;
                if ( $file === '' )
                    return false;
            } else {
                $file = $this->file_path($file);
            }


            if ( $wp_filesystem->is_file($file) ) {
                $filetime = $wp_filesystem->mtime($file);
            } else {
                $filetime = 0;
            }

            if ( (time() - $filetime) > $this->interval ) {
                $wp_filesystem->put_contents($file, $content);
            }
        }
    }
}