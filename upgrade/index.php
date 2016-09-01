<?php
/**
 * 2016 Jacques Grove
 *
 * @author    Jacques Grove
 * @copyright Jacques Grove Professional Web Development
 * @version   1.0.1
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * http://www.jacquesgrove.co.za
*/

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("Location: ../");
exit;
