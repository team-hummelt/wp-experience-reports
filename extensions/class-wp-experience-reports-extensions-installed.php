<?php

class Experience_Reports_Extensions_Installed {
    //INSTANCE
    private static $instance;#
    /**
     * @return static
     */

    public static function instance(): self
    {
        if (is_null((self::$instance))) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct(){}

    public function experience_reports_installed_extensions() {
        $prevDir = $this->check_extensions_folder(WP_EXPERIENCE_REPORTS_EXTENSION_PREVIEW_DIR);
        $installed = 0;

        if($prevDir){
           foreach ($prevDir as $key => $val) {
               $prevJson = WP_EXPERIENCE_REPORTS_EXTENSION_PREVIEW_DIR . $val . DIRECTORY_SEPARATOR ;
               if(is_file($prevJson.'extension.json')){
                   $conf = json_decode(file_get_contents($prevJson.'extension.json'));
                   $dir = WP_EXPERIENCE_REPORTS_EXTENSION_INSTALL_DIR . $val . DIRECTORY_SEPARATOR;
                   if(is_file($dir . $conf->filename . '.php')){
                       $installed++;
                       require_once $dir . $conf->filename . '.php';
                   }
               }
            }
        }
        if($installed > 0) {
            define('WP_EXPERIENCE_EXTENSION_NOT_ACTIVE', false);
        } else {
            define('WP_EXPERIENCE_EXTENSION_NOT_ACTIVE', true);
        }
    }

    private function check_extensions_folder($directory, $search = '') {
        $scanned = array_diff(scandir($directory), array('..', '.'));
        $folderArr = [];
        foreach ($scanned as $tmp) {
            if (is_dir($directory . $tmp)) {
                if ($search) {
                    if ($search == $tmp) {
                        return $tmp;
                    }
                }
                $folderArr[] = $tmp;
            }
        }
        return $folderArr;
    }
}

