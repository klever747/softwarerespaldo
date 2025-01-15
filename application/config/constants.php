<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
  |--------------------------------------------------------------------------
  | Display Debug backtrace
  |--------------------------------------------------------------------------
  |
  | If set to TRUE, a backtrace will be displayed along with php errors. If
  | error_reporting is disabled, the backtrace will not display, regardless
  | of this setting
  |
 */
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
  |--------------------------------------------------------------------------
  | File and Directory Modes
  |--------------------------------------------------------------------------
  |
  | These prefs are used when checking and setting modes when working
  | with the file system.  The defaults are fine on servers with proper
  | security, but you may wish (or even need) to change the values in
  | certain environments (Apache running a separate process for each
  | user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
  | always be used to set the mode correctly.
  |
 */
defined('FILE_READ_MODE') OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE') OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE') OR define('DIR_WRITE_MODE', 0755);

/*
  |--------------------------------------------------------------------------
  | File Stream Modes
  |--------------------------------------------------------------------------
  |
  | These modes are used when working with fopen()/popen()
  |
 */
defined('FOPEN_READ') OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE') OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE') OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE') OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE') OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE') OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT') OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT') OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
  |--------------------------------------------------------------------------
  | Exit Status Codes
  |--------------------------------------------------------------------------
  |
  | Used to indicate the conditions under which the script is exit()ing.
  | While there is no universal standard for error codes, there are some
  | broad conventions.  Three such conventions are mentioned below, for
  | those who wish to make use of them.  The CodeIgniter defaults were
  | chosen for the least overlap with these conventions, while still
  | leaving room for others to be defined in future versions and user
  | applications.
  |
  | The three main conventions used for determining exit status codes
  | are as follows:
  |
  |    Standard C/C++ Library (stdlibc):
  |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
  |       (This link also contains other GNU-specific conventions)
  |    BSD sysexits.h:
  |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
  |    Bash scripting:
  |       http://tldp.org/LDP/abs/html/exitcodes.html
  |
 */
defined('EXIT_SUCCESS') OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR') OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG') OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE') OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS') OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT') OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE') OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN') OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX') OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/* * **** */
define('PLANTILLA', "plantilla_uno/");
define("PRODUCT_FREE_SHIPPING", "AGR_P_FREESHIPPING_001_001");
define('ESTADO_INACTIVO', "I");
define('ESTADO_ACTIVO', "A");
define('ESTADO_ERROR', "E");
define("ESTADO_ORDEN_PROCESADA", "P");

define('RUTA_IMG', "firma_generica.png");
#DESCRIPCION: Se define una variable global para el color del icono al crear los boxes
define('BACKGROUND_BOXES', 'style="background-color: #C5F8F9"');
define('ESTADO_ORDEN_LOGISTICA', 'L');
define('ESTADO_ORDEN_ACTUALIZADA', 'Z');
define('ESTADO_ORDEN_PREPARADA', 'B');
define('ESTADO_ORDEN_EMPACADA', 'V');
define('ESTADO_ORDEN_CANCELADA', 'C');
define('ESTADO_ORDEN_REENVIADA', 'R');
define('ESTADO_ORDEN_CLONADA', 'W');

define('FORMATO_FECHA_DATEPICKER_JS', 'Y-MM-DD');
define('FORMATO_FECHA_DATEPICKER_FULL_JS', 'Y-MM-DD H:m:s');
define('FORMATO_FECHA_DATEPICKER_PHP', 'd-m-Y');
define('FORMATO_FECHA', 'Y-m-d');
define('FORMATO_FECHA_STORE', 'l, M d'); //Friday, Oct 09
define('FORMATO_FECHA_COMPLETO', 'Y-m-d\TH:i:sT');

define('DB_CON_ESQUEMAS', TRUE);

define('PANTALLA_LOGISTICA', 1);
define('PANTALLA_PREPARACION', 2);
define('PANTALLA_TERMINACION', 3);
define('PANTALLA_EMPAQUE', 4);
define('PANTALLA_MANUFACTURA', 5);

//define('PERFIL_SUPERADMINISTRADOR', 1);
//define('PERFIL_LOGISTICA', 2);
//define('PERFIL_TINTURADOR', 3);
//define('PERFIL_PREPARADOR', 4);
//define('PERFIL_EMPAQUE', 5);
//define('PERFIL_SOPORTE', 6);
//define('PERFIL_BONCHADO', 7);
//define('PERFIL_VENTAS', 8);

define('TARJETA_NORMALES','normal');
define('TARJETA_ETERNIZADAS','eternizadas');
/************************************/

define('FORMATO_10x15', array('llx' => 0, 'lly' => 0, 'urx' => 150, 'ury' => 100));
//define('FORMATO_10x15', array('llx' => 0, 'lly' => 0, 'urx' => 77, 'ury' => 102));
define('FORMATO_10x15_ROTACION', 90);
define('FORMATO_10x15_ROTACION_ETERNIZADAS', 90);
define('FORMATO_07x10', array('llx' => 0, 'lly' => 0, 'urx' => 77, 'ury' => 102));
define('FORMATO_07x10_ROTACION', 90);
define('FORMATO_07x10_ROTACION_ETERNIZADAS', 90);

define('FINCA_ROSAHOLICS_ID', 1);
define('CAJA_NODEFINIDA_ID', 1);
define('CAJA_CUTE_ID',2);
define('CAJA_PERFECT_ID',3);
define('CAJA_ABUNDANT_ID',4);
define('CAJA_FONDO_M_ID',5);
define('CAJA_QB_S_CORTADA_ID',6);
define('CAJA_HB_M_ID',7);
define('CAJA_QB_S_ID',8);
define('CAJA_QB_M_ID',9);
define('CAJA_QB_L_ID',10);
define('CAJA_FONDO_S_ID',11);
define('CAJA_BOX_L_ID',12);
define('CAJA_BOX_S_ID',13);
define('CAJA_BOX_M_ID',14);