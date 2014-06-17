<?php
/**
 * Plugin Name: DirectLiquidation
 * Description: DirectLiquidation
 * Version: 1.0
 * Author: Mikhail V Trusfus
 *
 * @package DirectLiquidation
 * @author Mikhail V Trusfus
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

spl_autoload_register(
    function ($className) {
        if (preg_match('/^DL_.*$/i', $className)) {
            include dirname(__FILE__) . "/classes/".strtolower($className).".php";
        }
    }
);
if (!class_exists('PHPExcel', false)) {
    include dirname(__FILE__) . '/lib/PHPExcel/Classes/PHPExcel.php';
}
DL_Plugin::init(__FILE__);
