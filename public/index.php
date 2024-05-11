<?php

/**
 *  --- Lógica del script --- 
 * 
 * Establece conexión a la base de datos PDO
 * Si el usuario ya está validado
 *   Si se solicita cerrar la sesión
 *     Destruyo la sesión
 *     Invoco la vista del formulario de login
 *    Sino redirección a juego para jugar una partida
 *  Sino 
 *   Si se pide procesar los datos del formulario
 *       Lee los valores del formulario
 *       Si los credenciales son correctos
 *       Redirijo al cliente al script de juego con una nueva partida
 *        Sino Invoco la vista del formulario de login con el flag de error
 *   Sino (En cualquier otro caso)
 *      Invoco la vista del formulario de login
 */
require "../vendor/autoload.php";
require "../src/error_handler.php";

use eftec\bladeone\BladeOne;
use Dotenv\Dotenv;
use App\BD\BD;
use App\Modelo\Usuario;
use App\DAO\UsuarioDAO;

session_start();

// Inicializa el acceso a las variables de entorno

$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

// Inicializa el acceso a las variables de entorno

$views = __DIR__ . '/../vistas';
$cache = __DIR__ . '/../cache';
$blade = new BladeOne($views, $cache, BladeOne::MODE_DEBUG);

// Establece conexión a la base de datos PDO
try {
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $database = $_ENV['DB_DATABASE'];
    $usuario = $_ENV['DB_USUARIO'];
    $password = $_ENV['DB_PASSWORD'];
    $bd = BD::getConexion($host, $port, $database, $usuario, $password);
} catch (PDOException $error) {
    echo $blade->run("cnxbderror", compact('error'));
    die;
}

$usuarioDAO = new UsuarioDAO($bd);

// Si el usuario ya está validado
if (isset($_SESSION['usuario'])) {

    // Si se solicita cerrar la sesión
    if (filter_has_var(INPUT_GET, 'botonlogout')) {

        // Destruyo la sesión
        session_unset();
        session_destroy();
        setcookie(session_name(), '', 0, '/');

        // Invoco la vista del formulario de login
        echo $blade->run("formlogin");

        //Si se solicita modificar datos usuario    
    } elseif (filter_has_var(INPUT_GET, 'botonmodificar')) {

        echo $blade->run("formmodifica");

        //Si se solicita la baja    
    } elseif (isset($_REQUEST['botonbaja'])) {
        $usuario = $_SESSION['usuario'];
        $usuarioDAO->elimina($usuario->getId());
        session_unset();
        session_destroy();
        setcookie(session_name(), '', 0, '/');
        echo $blade->run("formlogin", ['mensaje' => 'Baja realizada con éxito']);
        die;
    } else {
        // Redirijo al cliente al script de gestión del juego
        header("Location:juego.php?botonnuevapartida");
        die;
    }

// Sino 
} else {

    if (filter_has_var(INPUT_POST, 'botonproclogin')) {

        // Lee los valores del formulario
        $nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW));
        $pwd = trim(filter_input(INPUT_POST, 'pwd', FILTER_UNSAFE_RAW));
        $usuario = $usuarioDAO->recuperaPorCredencial($nombre, $pwd);

        // Si los credenciales son correctos
        if ($usuario) {
            $_SESSION['usuario'] = $usuario;
            // Redirijo al cliente al script de juego con una nueva partida
            header("Location:juego.php?botonnuevapartida");
            die;
        }

        // Si los credenciales son incorrectos
        else {
            // Invoco la vista del formulario de login con el flag de error activado
            echo $blade->run("formlogin", ['error' => true]);
            die;
        }

        //Si queremos crear usuario
    } elseif (isset($_REQUEST['botonRegistro'])) {

        echo $blade->run("formregistro");
        die;
    } elseif (isset($_REQUEST['botonRegistrar'])) {

        $nombreError = '';
        $pwdError = '';
        $emailError = '';

        /* function esNombreValido(string $nombre): bool {
          return preg_match("/^\w{3,15}$/", $nombre);
          }
         * 
         *  $nombreError = empty($nombre) || !esNombreValido($nombre);
         */

        $nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW));
        if (empty($nombre)) {
            $nombreError = 'Este campo no puede estar vacío';
        } elseif (!preg_match("/^[a-zA-Z0-9_]{3,15}$/", $nombre)) {
            $nombreError = 'Formato de nombre no válido';
        }

        /* $pwd = filter_input(INPUT_POST, 'pwd', FILTER_UNSAFE_RAW);
          if (empty($pwd)) {
          $pwdError = 'Este campo no puede estar vacío';
          } elseif (!preg_match('/^\d{6,}$/', $pwd)) {
          $pwdError = 'La contraseña debe tener más de 6 dígitos.';
          } */

        $pwd = filter_input(INPUT_POST, 'pwd', FILTER_UNSAFE_RAW);

        if (empty($pwd)) {
            // La clave es obligatoria
            $pwdError = 'La contraseña es obligatoria';
        } elseif (!filter_var($pwd, FILTER_VALIDATE_INT) || strlen($pwd) !== 6) {
            // La clave no es un número de 6 dígitos
            $pwdError = 'La contraseña debe ser un número de 6 dígitos';
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_UNSAFE_RAW);
        if (!empty($email) && !preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
            $emailError = 'Formato de email no válido';
        }
        /* // Validación de correo que puede ser vacío o con e lformato correcto
          function esEmailValido(string $email): bool {
          return (empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL));
          } */
        $errores = [$nombreError, $pwdError, $emailError];
        $numErrores = count(array_filter($errores)) > 0;

        if (!$numErrores) {
            // Verificar si el usuario ya existe en la base de datos
            $usuarioExistente = $usuarioDAO->recuperaPorNombre($nombre);

            if (!$usuarioExistente) {
                // Si el usuario no existe, crea uno nuevo
                $usuarioNuevo = new Usuario($nombre, $pwd, $email);

                try {
                    $usuarioCreado = $usuarioDAO->crea($usuarioNuevo);
                    $usuarioNuevo->setId($usuarioCreado);

                    // Mostrar mensaje de usuario creado
                    ?>
                    <script>
                        window.onload = function () {
                            alert("Usuario creado");
                        };
                    </script>
                    <?php

                    echo 'Usuario creado';
                    echo $blade->run('formlogin');
                    die;
                } catch (PDOException $ex) {
                    // Si ocurre un error al crear el usuario, mostrar mensaje y volver al formulario de registro
                    ?>
                    <script>
                        window.onload = function () {
                            alert("Ocurrió un error y no se ha podido crear el usuario.");
                        };
                    </script>
                    <?php

                    echo $blade->run('formregistro');
                    die;
                }
            } else {
                // Si el usuario ya existe, mostrar mensaje de usuario existente y volver al formulario de registro
                ?>
                <script>
                    window.onload = function () {
                        alert("Usuario ya existente.");
                    };
                </script>
                <?php

                echo $blade->run('formregistro');
                die;
            }
        } else {
            echo $blade->run('formregistro', compact('nombre', 'pwd', 'email', 'nombreError', 'pwdError', 'emailError'));
            die;
        }

//SESIÓN ADMINISTRADOR
    } elseif (isset($_REQUEST['botonaccesoadmin'])) {
        echo $blade->run("formadmin");
        die;
    } elseif (isset($_REQUEST['botonprocloginadmin'])) {

        // Lee los valores del formulario
        $nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW));
        $pwd = trim(filter_input(INPUT_POST, 'pwd', FILTER_UNSAFE_RAW));
        $usuario = $usuarioDAO->recuperaPorCredencial($nombre, $pwd);

        // Si los credenciales son de ADMINISTRADOR
        if ($usuario) {

            $usuarioAdmin = $usuario->esAdministrador();
            if ($usuarioAdmin) {
                $_SESSION['usuario'] = $usuario;
                // Redirijo al admin a la vista de admin
                echo $blade->run("admin");
                die;
                
            } else {
                echo $blade->run("formadmin", ['error' => true]);
            }


            // Si los credenciales son incorrectos
        } else {
            // Invoco la vista del formulario de login con el flag de error activado
            echo $blade->run("formadmin", ['error' => true]);
            die;
        }
    } elseif (isset($_REQUEST['botonListaUsuarios'])) {
        $_SESSION['usuarios'] = $usuarioDAO->recuperaTodo();
        try {
            $panelUsuarios = [];
            foreach ($usuarios as $usuario) {
                $panelUsuarios[] = [$usuario->getNombre(), $usuario->partidasJugadas(), $usuario->puntuacionTotal()];
            }
            echo $blade->run("admin", compact('panelUsuarios', 'usuario'));
            die;
        } catch (Exception) {
            echo 'Error generando lista.';
        }
    } else {
        echo $blade->run("formlogin");
    }
}    