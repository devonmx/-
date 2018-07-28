<?php
ob_start();
session_start();

require_once 'medoo.php';
require_once '_db.php';

define("COOKIE_TIME_OUT", 10); // Especifica cookie timeout en dias (default 10 dias)
define("base_dir", realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR);
define("upload_dir", base_dir . 'cache' . DIRECTORY_SEPARATOR);

// Nombre del Sitio
$proyecto = 'Provoke';
// Obtiene la url actual del sitio
$url_actual = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
// VARIABLE BASE_URL para la ruta absoluta del sitio y los datos de conexion locales y remotos
if ($_SERVER['HTTP_HOST'] === "localhost") {
    define("base_url", "http://" . $_SERVER['HTTP_HOST'] . "/provoke.com/");
} else {
    define("base_url", "http://" . $_SERVER['HTTP_HOST'] . "/provoke.com/");
}

// Listado de las modulos a las cuales tendra acceso
$sec = "";
// Indica en que seccion se encuenta
$seccion_actual = "";

// MANTENIMIENTO 1 NORMAL 0
$url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$man = "0";
if ($url !== base_url && $man === "1") {
    header("location: " . base_url . "logout.php");
}

// ARRAY de las Secciones Administrables por cada usuario
if(isset($_SESSION["session"])){
    $sec = explode("**", $_SESSION['modulos']);
}

// require_once '_modulos.php';

//Configuracion para poner los errores y warnings en los logs
ini_set("log_errors", "1");
ini_set("error_log", base_url . "errors.log");
ini_set("display_errors", "0");


// FUNCIONES DE LOGIN - CONTRASEÑAS - CONTACTO
if ($_POST) {
    switch ($_POST['accion']) {
        case 'contacto':contacto();
        break;

        case 'login': login();
        break;

        case 'forgot': forgot();
        break;

        case 'recovery': recovery();
        break;
    }
}

function filtro($data) {
    // $data = trim(htmlentities(strip_tags($data)));

    // if (get_magic_quotes_gpc()){
    //     $data = stripslashes($data);
    // }

    // $data = mysql_real_escape_string($data);

    return $data;
}

function login() {
    global $db;
    foreach ($_POST as $key => $value) {
        $data[$key] = filtro($value); // POST pasa por un filtro   filtro($value);
    }
    
    if ($data['usuario'] !== "" || $data["password"] !== "") {
        $usuario = trim($data['usuario']);
        $password = md5($data['password']);
        
        if (strpos($usuario, '@') === false) {
            $condicion = "usuario_cms";
        } else {
            $condicion = "correo_cms";
        }

        $consulta = $db->get("cms", "*", ["AND" => [$condicion => $usuario,"clave_cms" => $password]]);

        if ($consulta['status_cms'] === "1") {
            echo "suspended";
        } else if ($consulta !== false) {
            $sucursalesA = explode(",", $consulta['sucursales_cms']);

            $_SESSION['id'] = $consulta['id_cms'];
            $_SESSION['nombre'] = $consulta['nombre_cms'];
            $_SESSION['nivel'] = $consulta['permisos_cms'];
            $_SESSION['ultimoacceso'] = $consulta['login_cms'];
            $_SESSION['session'] = "admin";
            $_SESSION['modulos'] = $consulta['modulos_cms'];
            $_SESSION['sucursales'] = $sucursalesA;

            // Actualiza el tiempo y la key de cookies
            $stamp = time();
            $key = GenKey();

            $actualiza = $db->update("cms", ["login_cms" => $stamp, "key_cms" => $key], ["id_cms" => $_SESSION['id']]);

            setcookie("id", $_SESSION['id'], time() + 60 * 60 * 24 * COOKIE_TIME_OUT, "/");
            setcookie("key", sha1($key), time() + 60 * 60 * 24 * COOKIE_TIME_OUT, "/");
            setcookie("nombre", $_SESSION['nombre'], time() + 60 * 60 * 24 * COOKIE_TIME_OUT, "/");

            echo "true";
        } else {
            echo "false";
        }
    } else {
        echo "false";
    }
}

function forgot() {
    global $db;
    $email = trim(strtolower($_POST['email']));
    mysql_query("SET NAMES 'utf8'");
    $consulta = $db->get("cms", "*", ["correo_cms" => $email]);
    $usuario = $consulta['nombre_cms'];
    $hash1 = md5($consulta['id_cms']);
    $hash2 = md5($consulta['correo_cms']);

    if($consulta === false){
     echo "false";
 }else{
    $para = $email;
    $de = 'Genotipo <soporte@genotipo.com>';
    $subjet = 'Recuperacion de cuenta backend.';
    $tipo = 'Recuperaci&oacute;n de cuenta';
    $titulo = 'Recuperaci&oacute;n de Contraseña';
    $msg = '
    Estimado(a) <strong>' . $usuario . '</strong> se ha registrado una solicitud de cambio de contraseña en su cuenta.
    <br/><br/>
    Si usted no ha realizado esta solicitud, ignore este e-mail, en caso contrario para <strong>reiniciar</strong> su contraseña de <strong>click</strong> en el siguiente link:
    <br/><br/>
    <a href="' . base_url . '?recovery=' . $hash1 . $hash2 . '">Restablecer contraseña ahora.</a>
    <br/><br/>
    Que tenga buen d&iacute;a.';

    envio_email($para, $de, $subjet, $tipo, $titulo, $msg);
    echo "true";
}
}

function recovery() {
    global $db;
    $res1 = substr($_POST['hash'], 0, 32);
    $res2 = substr($_POST['hash'], 32, 64);

    mysql_query("SET NAMES 'utf8'");
    $consulta = $db->query("SELECT * FROM cms WHERE md5(id_cms) = '$res1' AND md5(correo_cms) = '$res2'")->fetchAll();
    
    $password = md5($_POST['password']);

    if(empty($consulta)) {
        echo "false";
    }else{
        $actualiza = $db->update("cms", ["clave_cms" => $password], ["id_cms" => $consulta[0]["id_cms"]]);
        
        $para = $consulta[0]['correo_cms'];
        $de = 'Genotipo <soporte@genotipo.com>';
        $subjet = 'Cambio de contraseña Nanodepot CRM.';
        $tipo = 'Cambio de contraseña';
        $titulo = 'Cambio de contraseña exitoso.';
        $msg = '
        Estimado(a) <strong>' . $consulta[0]['nombre_cms'] . '</strong> su cambio de contraseña se ha realizado con &eacute;xito.
        <br/><br/>
        Si usted no ha realizado este proceso favor de contactarse al corporativo para la verificaci&oacute;n de su cuenta.
        <br/><br/>
        Que tenga buen d&iacute;a.';

        envio_email($para, $de, $subjet, $tipo, $titulo, $msg);

        echo "true";
    }
}

function logout() {
    global $db;
    session_start();

    $sess_user_id = strip_tags(mysql_real_escape_string($_SESSION['id']));
    $cook_user_id = strip_tags(mysql_real_escape_string($_COOKIE['id']));

    if (isset($sess_user_id) || isset($cook_user_id)) {
        $actualiza = $db->update("cms", ["key_cms" => "", "login_cms" => ""], ["id_cms" => $sess_user_id]);
    }

    /* Elimina las sesiones*************** */
    unset($_SESSION['id']);
    unset($_SESSION['nombre']);
    unset($_SESSION['nivel']);
    unset($_SESSION['ultimoacceso']);
    unset($_SESSION['session']);
    unset($_SESSION['modulos']);
    unset($_SESSION['HTTP_USER_AGENT']);
    session_unset();
    session_destroy();


    /* Borra las cookies ****************** */
    setcookie("id", '', time() - 60 * 60 * 24 * COOKIE_TIME_OUT, "/");
    setcookie("nombre", '', time() - 60 * 60 * 24 * COOKIE_TIME_OUT, "/");
    setcookie("key", '', time() - 60 * 60 * 24 * COOKIE_TIME_OUT, "/");

    header("Location: " . base_url);
}

// VERIFICA EL LOGIN
function page_protect() {
    global $db;
    global $sec;
    global $seccion_actual;

    // Verificamos la seccion donde se esta trabajando
    $segmentos = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
    $seccion = $segmentos[count($segmentos) - 1];
    $seccion_actual = trim($seccion, '.php');
    // Verifica si entra al modelo de nuestros modulos para evitar cualquier acceso a nuestro REST
    if($seccion_actual === "modelo"){
        $seccion = $segmentos[count($segmentos) - 2];
        $seccion_actual = trim($seccion, '.php');
    }
    // Verifica si existe una session una vez logueado
    if ($_SESSION['session'] != "admin") {
        header("Location: " . base_url);
    }

    // Verifica si puedes acceder al Modulo si no bye
    if (in_array("todos", $sec) || in_array($seccion_actual, $sec)) {
    }else{
        header("Location: " . base_url);
        exit();
    }

    /* Para evitar secuestros de sesión mediante la comprobación de agente de usuario */
    if (isset($_SESSION['HTTP_USER_AGENT'])) {
        if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT'])) {
            logout();
            exit;
        }
    }

    // Antes de permitir las sesiones, tenemos que comprobar la clave de autenticación - ckey ctime se almacenan en la base de datos
    /* Si la sesión no se establece, comprueba si hay cookies de Recordar */
    if (!isset($_SESSION['id']) && !isset($_SESSION['nombre'])) {
        if (isset($_COOKIE['id']) && isset($_COOKIE['key'])) {

            /* Comprobamos tiempo de caducidad de la Cookie contra la almacenada en la base de datos */
            $cookie_user_id = filtro($_COOKIE['id']);
            $rs_ctime = $db->get("cms", ["key_cms", "login_cms"], ["id_cms" => $cookie_user_id]);
            // Expiración de la cookie
            if ((time() - $rs_ctime["key_cms"]) > 60 * 60 * 24 * COOKIE_TIME_OUT) {
                logout();
            }
            /* Control de seguridad con las cookies no son de confianza. No te fíes de valor almacenado en la Cookie.
            /* También hacemos comprobación de autenticación del `ckey` almacenada en cookies que se almacena en la base de datos durante el inicio de sesión */

            if (!empty($rs_ctime["key_cms"]) && is_numeric($_COOKIE['id']) && isUserID($_COOKIE['nombre']) && $_COOKIE['key'] == sha1($rs_ctime["key_cms"])) {
                session_regenerate_id(); // Contra los ataques de fijación de sesión.

                $_SESSION['id'] = $_COOKIE['id'];
                $_SESSION['nombre'] = $_COOKIE['nombre'];
                /* Nivel de usuario en consulta de base de datos en lugar de almacenar en las cookies */
                $user_level = $db->get("cms", "*", ["id_cms" => $_SESSION["id"]]);
                $_SESSION['nivel'] = $user_level["permisos_cms"];
                $_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
            } else {
                logout();
            }
        } else {
            header("Location: " . base_url);
            exit();
        }
    }
}


function GenKey($length = 7) {
    $password = "";
    $possible = "0123456789abcdefghijkmnopqrstuvwxyz";

    $i = 0;

    while ($i < $length) {


        $char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);


        if (!strstr($password, $char)) {
            $password .= $char;
            $i++;
        }
    }

    return $password;
}

function envio_email($para, $de, $subjet, $tipo, $titulo, $msg) {
    $logo = base_url."img/logo_header.png";
    $link = base_url;
    $tlink = "sistema.controldecarga.com";

    $destinatario = $para;
    $asunto = $subjet;
    $color = "#303030";
    $mensaje = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Documento sin título</title>
    </head>
    <body style="background-color: #ddd;">
        <table id="pageContainer" width="100%" align="center" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse; background-repeat:repeat; "> 
            <tbody>
                <tr> 
                    <td style="padding:30px 20px 40px 20px;"> 
                        <table width="600" align="center" cellpadding="0" cellspacing="0" style="border-collapse:collapse; text-align:left; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:12px; line-height:15pt; color:#777777;"> 
                            <tbody> 
                                <tr> 
                                    <td bgcolor="'.$color.'" colspan="2" height="7" style="font-size:2px; line-height:0px;">
                                        <img alt="" height="7" src="http://www.genotipo.com/img/mail/blank.gif" width="600" align="left" vspace="0" hspace="0" border="0" style="display:block;">
                                    </td> 
                                </tr> 
                                <tr> 
                                    <td bgcolor="'.$color.'" width="255" valign="middle" style="padding:25px 28px 25px 28px; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:100%; color:'.$color.';"> 
                                        <a href="http://www.genotipo.com/"><img alt="Logo" src="' . $logo . '" align="left" border="0" vspace="0" hspace="0" style="display:block;"> </a>
                                    </td> 
                                    <td bgcolor="'.$color.'" width="255" valign="middle" style="padding:20px 20px 15px 0; font-family:Arial, Helvetica, sans-serif; font-size:11px; line-height:100%; color:#777777; text-align:right;"> 
                                        <table width="254" align="right" cellpadding="0" cellspacing="0" style="border-collapse:collapse; text-align:center; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:12px; line-height:100%; color:#777777;"> 
                                            <tbody>
                                                <tr> 
                                                    <td width="66" valign="top" style="line-height:100%; color:#fff;"> 
                                                        <img alt="●" src="http://www.genotipo.com/img/mail/calendarIcon.png" height="32" width="32" border="0" vspace="0" hspace="17" style="display:block;"> 
                                                        ' . ucfirst(strftime("%b %d")) . ' 
                                                    </td> 
                                                    <td width="20" style="padding:0 10px; line-height:100%; text-align:center;">
                                                        <img alt="" src="http://www.genotipo.com/img/mail/separatorw.png" width="20" height="50" border="0" style="vertical-align:0px; display:block;">
                                                    </td> 
                                                    <td width="64" valign="top" style="line-height:100%;"> 
                                                        <a href="mailto:' . $de . '" style="text-decoration:none; color:#fff; display:block; line-height:100%;">
                                                            <img alt="●" src="http://www.genotipo.com/img/mail/forwardIcon.png" height="32" width="32" border="0" vspace="0" hspace="11" style="display:block;"> 
                                                            Responder
                                                        </a> 
                                                    </td> 
                                                    <td width="20" style="padding:0 10px; line-height:100%; text-align:center;">
                                                        <img alt="" src="http://www.genotipo.com/img/mail/separatorw.png" width="20" height="50" border="0" style="vertical-align:0px; display:block;">
                                                    </td> 
                                                    <td width="54" valign="top" style="line-height:100%;"> 
                                                        <a href="' . $link . '" style="text-decoration:none; color:#fff; display:block; line-height:100%;">
                                                            <img alt="●" src="http://www.genotipo.com/img/mail/websiteIcon.png" height="32" width="32" border="0" vspace="0" hspace="11" style="display:block;"> 
                                                            Backend
                                                        </a> 
                                                    </td> 
                                                </tr> 
                                            </tbody>
                                        </table> 
                                    </td> 
                                </tr> 
                                <tr> 
                                    <td colspan="2" height="11" style="font-size:2px; line-height:0px;">
                                        <img alt="" src="http://www.genotipo.com/img/mail/divider.png" height="11" width="600" align="left" border="0" vspace="0" hspace="0" style="display:block;">
                                    </td> 
                                </tr> 
                            </tbody>
                        </table> 

                        <table bgcolor="#ffffff" width="600" align="center" cellpadding="0" cellspacing="0" style="border-collapse:collapse; text-align:left; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:12px; line-height:15pt; color:#777777;"> 
                            <tbody>
                                <tr> 
                                    <td style="padding-top:20px; padding-right:30px; padding-left:30px; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:100%; color:#aaaaaa;"> 
                                        <img alt="" src="http://www.genotipo.com/img/mail/dateIcon.png" height="14" width="12" border="0" vspace="0" hspace="0" style="vertical-align:-1px;" />&nbsp;&nbsp; ' . date("d.m.y") . ' &nbsp;&nbsp;
                                        <img alt="" src="http://www.genotipo.com/img/mail/categoryIcon.png" height="14" width="15" border="0" vspace="0" hspace="0" style="vertical-align:-2px;" />&nbsp;&nbsp; ' . $tipo . ' 
                                    </td> 
                                </tr> 
                                <tr> 
                                    <td style="padding-top:20px; padding-right:40px; padding-left:30px; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:15pt; color:#777777;"> 
                                        <p style="font-family: Segoe UI, Helvetica Neue, Helvetica, Arial, sans-serif; font-size:30px; line-height:30pt; color:#3b5167; font-weight:300; margin-top:0; margin-bottom:20px !important; padding:0; text-indent:-3px;">' . $titulo . '</p> 
                                    </td> 
                                </tr> 
                                <tr> 
                                    <td style="padding-right:30px; padding-bottom:30px; padding-left:30px; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:15pt; color:#777777;">   
                                        ' . $msg . '
                                    </td> 
                                </tr> 
                                <tr> 
                                    <td height="11" style="font-size:2px; line-height:0px;">
                                        <img alt="" src="http://www.genotipo.com/img/mail/divider.png" height="11" width="600" align="left" border="0" vspace="0" hspace="0" style="display:block;">
                                    </td> 
                                </tr> 
                            </tbody>
                        </table> 
                        <table bgcolor="#f4f4f4" width="600" align="center" cellpadding="0" cellspacing="0" style="border-collapse:collapse; text-align:left; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:12px; line-height:15pt; color:#777777;"> 
                            <tbody>
                                <tr> 
                                    <td> 
                                        <table width="600" cellpadding="0" cellspacing="0" style="border-collapse:collapse; text-align:left; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:12px; line-height:15pt; color:#777777;"> 
                                            <tbody>
                                                <tr> 
                                                    <td width="30">
                                                        <img alt="" height="10" src="http://www.genotipo.com/img/mail/blank.gif" width="30" align="left" vspace="0" hspace="0" border="0" style="display:block;">
                                                    </td> 
                                                    <td width="160" valign="top" style="padding-top:30px; padding-bottom:30px; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:15pt; color:#777777;"> 
                                                        Copyright &COPY; ' . date("Y") . '<br/>
                                                        <a style="text-decoration:underline; color:'.$color.';" href="' . $link . '">' . $tlink . '</a> 
                                                        <br/>
                                                        All rights reserved.
                                                    </td> 
                                                    <td width="30">
                                                        <img alt="" height="10" src="http://www.genotipo.com/img/mail/blank.gif" width="30" align="left" vspace="0" hspace="0" border="0" style="display:block;">
                                                    </td> 
                                                    <td width="160" valign="top" style="padding-top:34px; padding-bottom:30px; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:15pt; color:#777777;"> 
                                                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; text-align:left; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:12px; line-height:100%; color:#777777;"> 
                                                            <tbody>
                                                                <tr> 
                                                                    <td class="footer_list_image" width="20" valign="top" style="padding:0 0 9px 0;">
                                                                        <img alt="●" src="http://www.genotipo.com/img/mail/homeIcon.png" width="13" height="12" border="0" align="left" style="display:block;">
                                                                    </td> 
                                                                    <td class="footer_list" width="140" valign="top" style="padding:0 0 9px 0; line-height:9pt;"> 
                                                                        <a href="' . $link . '" style="text-decoration:underline; color:'.$color.'; line-height:9pt;"> ' . $tlink . '</a> 
                                                                    </td> 
                                                                </tr> 
                                                                <tr> 
                                                                    <td class="footer_list_image" width="20" valign="top" style="padding:0 0 9px 0;">
                                                                        <img alt="●" src="http://www.genotipo.com/img/mail/emailIcon.png" width="12" height="12" border="0" align="left" style="display:block;">
                                                                    </td> 
                                                                    <td class="footer_list" width="140" valign="top" style="padding:0 0 9px 0; line-height:9pt;"> 
                                                                        <a href="mailto:' . $de . '" style="text-decoration:underline; color:'.$color.'; line-height:9pt;"> ' . $de . '</a> 
                                                                    </td> 
                                                                </tr> 
                                                                <tr> 
                                                                    <td class="socialIcons" colspan="2" style="padding-top:17px; padding-bottom:5px;"> 
                                                                        <a href="#"><img alt="Facebook" src="http://www.genotipo.com/img/mail/facebookIcon.png" border="0" vspace="0" hspace="0"></a>&nbsp;&nbsp; 
                                                                        <a href="#"><img alt="Twitter" src="http://www.genotipo.com/img/mail/twitterIcon.png" border="0" vspace="0" hspace="0"></a>&nbsp;&nbsp; 
                                                                    </td> 
                                                                </tr> 
                                                            </tbody>
                                                        </table> 
                                                    </td> 
                                                    <td width="30">
                                                        <img alt="" height="10" src="http://www.genotipo.com/img/mail/blank.gif" width="30" align="left" vspace="0" hspace="0" border="0" style="display:block;">
                                                    </td> 
                                                    <td width="160" valign="top" style="padding-top:30px; padding-bottom:30px; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:15pt; color:#777777;"> 
                                                        <strong>Email de Notificación</strong><br/> Estos emails unicamente son para referencias futuras.<br/><br/>
                                                    </td> 
                                                    <td width="30">
                                                        <img alt="·" height="10" src="http://www.genotipo.com/img/mail/blank.gif" width="30" align="left" vspace="0" hspace="0" border="0" style="display:block;">
                                                    </td> 
                                                </tr> 
                                            </tbody>
                                        </table> 
                                    </td> 
                                </tr> 
                                <tr> 
                                    <td bgcolor="'.$color.'" height="7" style="font-size:2px; line-height:0px;"><img alt="" height="7" src="http://www.genotipo.com/img/mail/blank.gif" width="600" align="left" vspace="0" hspace="0" border="0" style="display:block;"></td> 
                                </tr> 
                            </tbody>
                        </table> 
                    </td> 
                </tr> 
            </tbody>
        </table>
    </body>
    </html>
    ';

    $headers = "From: Genotipo <soporte@genotipo.com> \r\n";
    $headers .= "X-Mailer: PHP5\n";
    $headers .= 'MIME-Version: 1.0' . "\n";
    $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

    mail($destinatario, $asunto, $mensaje, $headers);
}


// REEMPLAZA CARACTERES PARA URL AMIGABLE
function replaceUrl($string){
    return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
}


// REDIMENSIONA GENERAL
function redim($ruta1, $ruta2, $ancho, $alto) {
    # se obtiene la dimension y tipo de imagen
    $datos = getimagesize($ruta1);

    $ancho_orig = $datos[0]; # Anchura de la imagen original
    $alto_orig = $datos[1];    # Altura de la imagen original
    $tipo = $datos[2];

    if ($tipo == 1) { # GIF
        if (function_exists("imagecreatefromgif"))
            $img = imagecreatefromgif($ruta1);
        else
            return false;
    }
    else if ($tipo == 2) { # JPG
        if (function_exists("imagecreatefromjpeg"))
            $img = imagecreatefromjpeg($ruta1);
        else
            return false;
    }
    else if ($tipo == 3) { # PNG
        if (function_exists("imagecreatefrompng"))
            $img = imagecreatefrompng($ruta1);
        else
            return false;
    }

    # Se calculan las nuevas dimensiones de la imagen
    if ($ancho_orig > $alto_orig) {
        $ancho_dest = $ancho;
        $alto_dest = ($ancho_dest / $ancho_orig) * $alto_orig;
    } else {
        $alto_dest = $alto;
        $ancho_dest = ($alto_dest / $alto_orig) * $ancho_orig;
    }

    // imagecreatetruecolor, solo estan en G.D. 2.0.1 con PHP 4.0.6+
    $img2 = @imagecreatetruecolor($ancho_dest, $alto_dest) or $img2 = imagecreate($ancho_dest, $alto_dest);

    // Redimensionar
    // imagecopyresampled, solo estan en G.D. 2.0.1 con PHP 4.0.6+
    @imagecopyresampled($img2, $img, 0, 0, 0, 0, $ancho_dest, $alto_dest, $ancho_orig, $alto_orig) or imagecopyresized($img2, $img, 0, 0, 0, 0, $ancho_dest, $alto_dest, $ancho_orig, $alto_orig);

    // Crear fichero nuevo, según extensión.
    if ($tipo == 1) // GIF
    if (function_exists("imagegif"))
        imagegif($img2, $ruta2);
    else
        return false;

    if ($tipo == 2) // JPG
    if (function_exists("imagejpeg"))
        imagejpeg($img2, $ruta2);
    else
        return false;

    if ($tipo == 3)  // PNG
    if (function_exists("imagepng"))
        imagepng($img2, $ruta2);
    else
        return false;

    return true;
}

// RESIZE DE LAS IMAGENES CON FORMATO TOTALMENTE CUADRADO
if (!function_exists("create_square_image")) {

    function create_square_image($original_file, $destination_file = NULL, $square_size = 100) {
        // get width and height of original image
        $imagedata = getimagesize($original_file);
        $original_width = $imagedata[0];
        $original_height = $imagedata[1];

        if ($original_width > $original_height) {
            $new_height = $square_size;
            $new_width = $new_height * ($original_width / $original_height);
        }
        if ($original_height > $original_width) {
            $new_width = $square_size;
            $new_height = $new_width * ($original_height / $original_width);
        }
        if ($original_height == $original_width) {
            $new_width = $square_size;
            $new_height = $square_size;
        }

        $new_width = round($new_width);
        $new_height = round($new_height);

        // load the image
        if (substr_count(strtolower($original_file), ".jpg") or substr_count(strtolower($original_file), ".jpeg") or substr_count(strtolower($original_file), ".JPG") or substr_count(strtolower($original_file), ".JPEG")) {
            $original_image = imagecreatefromjpeg($original_file);
        }
        if (substr_count(strtolower($original_file), ".gif")) {
            $original_image = imagecreatefromgif($original_file);
        }
        if (substr_count(strtolower($original_file), ".png")) {
            $original_image = imagecreatefrompng($original_file);
        }

        $smaller_image = imagecreatetruecolor($new_width, $new_height);
        $square_image = imagecreatetruecolor($square_size, $square_size);

        imagecopyresampled($smaller_image, $original_image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);

        if ($new_width > $new_height) {
            $difference = $new_width - $new_height;
            $half_difference = round($difference / 2);
            imagecopyresampled($square_image, $smaller_image, 0 - $half_difference + 1, 0, 0, 0, $square_size + $difference, $square_size, $new_width, $new_height);
        }
        if ($new_height > $new_width) {
            $difference = $new_height - $new_width;
            $half_difference = round($difference / 2);
            imagecopyresampled($square_image, $smaller_image, 0, 0 - $half_difference + 1, 0, 0, $square_size, $square_size + $difference, $new_width, $new_height);
        }
        if ($new_height == $new_width) {
            imagecopyresampled($square_image, $smaller_image, 0, 0, 0, 0, $square_size, $square_size, $new_width, $new_height);
        }


        // if no destination file was given then display a png      
        if (!$destination_file) {
            imagepng($square_image, NULL, 9);
        }

        // save the smaller image FILE if destination file given
        if (substr_count(strtolower($destination_file), ".jpg") or substr_count(strtolower($destination_file), ".jpeg")) {
            imagejpeg($square_image, $destination_file, 100);
        }
        if (substr_count(strtolower($destination_file), ".gif")) {
            imagegif($square_image, $destination_file);
        }
        if (substr_count(strtolower($destination_file), ".png")) {
            imagepng($square_image, $destination_file, 9);
        }

        imagedestroy($original_image);
        imagedestroy($smaller_image);
        imagedestroy($square_image);
    }

}