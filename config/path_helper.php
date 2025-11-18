<?php
class PathHelper {
    private static $projectFolder = 'HCI-Prototype';
    
    /**
     * Detect the correct base path based on current file location
     */
    public static function getBasePath() {
        $scriptPath = $_SERVER['SCRIPT_NAME'];
        $currentDir = dirname($scriptPath);
        $currentDir = rtrim($currentDir, '/');
        
        // Count how many directories deep we are from project root
        $pathParts = explode('/', $currentDir);
        $depth = count(array_filter($pathParts));
        
        // If we're in views/role directory, we need to go up 2 levels
        if (strpos($currentDir, 'views') !== false && isset($_SESSION['user'])) {
            return '../../';
        }
        // If we're in includes directory, go up 1 level
        elseif (strpos($currentDir, 'includes') !== false) {
            return '../';
        }
        // If we're in root
        else {
            return './';
        }
    }
    
    public static function getCssPath() {
        return self::getBasePath() . 'css/';
    }
    
    public static function getJsPath() {
        return self::getBasePath() . 'js/';
    }
    
    public static function getRolePath($role = null) {
        if (!$role && isset($_SESSION['user'])) {
            $role = $_SESSION['user']['role'];
        }
        return self::getBasePath() . 'views/' . $role . '/';
    }
    
    public static function getIncludesPath() {
        return self::getBasePath() . 'includes/';
    }
    
    public static function getCurrentPage() {
        return basename($_SERVER['PHP_SELF']);
    }
    
}
?>