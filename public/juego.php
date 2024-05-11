<?php

/**
 *  --- Lógica del script --- 
 * 
 * Establece conexión a la base de datos PDO
 * Si el usuario ya está validado
 *   Si se pide jugar con una letra
 *     Leo la letra
 *     Si no hay error en la letra introducida
 *       Solicito a la partida que compruebe la letra
 *     Invoco la vista de juego con los datos obtenidos
 *   Sino si se solicita una nueva partida
 *     Se crea una nueva partida
 *     Invoco la vista del juego para empezar a jugar
 *   Sino Invoco la vista de juego
 *  Sino (En cualquier otro caso)
 *      Invoco la vista del formulario de login
 */
require "../vendor/autoload.php";

use eftec\bladeone\BladeOne;
use Dotenv\Dotenv;
use App\Modelo\Hangman;
use App\Almacen\AlmacenPalabrasRest;

//use App\Almacen\AlmacenPalabrasFichero; 
//use App\Almacen\AlmacenPalabrasSoap; 

session_start();

define("MAX_NUM_ERRORES", 5);

$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$views = __DIR__ . '/../vistas';
$cache = __DIR__ . '/../cache';
$blade = new BladeOne($views, $cache, BladeOne::MODE_DEBUG);

// Si el usuario ya está validado
if (isset($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario'];
// Si se pide jugar con una letra
    if (isset($_POST['botonenviarjugada'])) {
// Leo la letra
        $letra = trim(filter_input(INPUT_POST, 'letra', FILTER_UNSAFE_RAW));
        $partida = $_SESSION['partida'];
// Compruebo si la letra no es válida (carácter no válido o ya introducida)
        $error = !$partida->esLetraValida($letra);
        // Si no hay error compruebo la letra
        if (!$error) {
            $partida->compruebaLetra(strtoupper($letra));
            if ($partida->esFin()) {
                $_SESSION['partidas'][] = $partida;
            }
        }
        // Sigo jugando
        echo $blade->run("juego", compact('usuario', 'partida', 'error'));
        die;
// Sino si se solicita una nueva partida
    /*} elseif (isset($_REQUEST['botonnuevapartida'])) { 
        $rutaFichero = $_ENV['RUTA_ALMACEN_PALABRAS']; 
        $almacenPalabras = new AlmacenPalabrasFichero($rutaFichero); 
        $partida = new Hangman($almacenPalabras, MAX_NUM_ERRORES);
        $_SESSION['partida'] = $partida;
    // Invoco la vista del juego para empezar a jugar
        echo $blade->run("juego", compact('usuario', 'partida'));
        die;*/
    } elseif (isset($_REQUEST['botonnuevapartida'])) { // Se arranca una nueva partida
        $rest = $_ENV['REST_ALMACEN_PALABRAS']; //$wsdl = $_ENV['WSDL_ALMACEN_PALABRAS'];
        $almacenPalabras = new AlmacenPalabrasRest($rest); //$almacenPalabras = new AlmacenPalabrasSoap($wsdl);
        $partida = new Hangman($almacenPalabras, MAX_NUM_ERRORES);
        $_SESSION['partida'] = $partida;
    // Invoco la vista del juego para empezar a jugar
        echo $blade->run("juego", compact('usuario', 'partida'));
        die;    
    } elseif (isset($_REQUEST['botonpuntuacionpartidas'])) {
        $partidas = $_SESSION['partidas'] ?? [];
        $panelPuntuacion = [];
        foreach ($partidas as $partida) {
            $panelPuntuacion[] = [$partida->getPalabraSecreta(), $partida->getNumErrores(), $partida->getPuntuacion()];
        }
        echo $blade->run("puntuacionpartidas", compact('panelPuntuacion', 'usuario'));
        die;
        
    } elseif (isset($_REQUEST['botonpista'])) {

        $partida = $_SESSION['partida'];
        $pista = $partida->getPista();
        header('Content-type: application/json');
        echo json_encode(['letra' => $pista]);
        die;
        
    } else { //En cualquier otro caso
        $partida = $_SESSION['partida'];
        echo $blade->run("juego", compact('usuario', 'partida'));
        die;
    }
// En otro caso se muestra el formulario de login
} else {
    echo $blade->run("formlogin");
    die;
}

