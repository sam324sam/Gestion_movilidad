<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error{
            color: red;
        }
        a{
            text-decoration: none;
            background-color: #4CAF50;
            font-size: 14px;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            cursor: pointer;
            margin-right: 10px;
        }
        a:hover{
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <form action="formularioTipado.php" method="post">
        <?php
        $archivos = array('taxis' => 'datos/taxis.txt', 'EMT' => 'datos/vehiculosEMT.txt', 'servicios' => 'datos/servicios.txt', 'residentesYHoteles' => 'datos/residentesYHoteles.txt', 'logistica' => 'datos/logistica.txt');
        $tipo = htmlspecialchars($_POST['tipo']);
        $archivo = $archivos[$tipo];
        // Variable para recoger errores en el formulario
        $error = array('matricula' => "", 'dato' => "", 'fecha' => "");
        if ($_SERVER["REQUEST_METHOD"] == "GET") { // Pintar formulario por default
            $tipo = htmlspecialchars($_GET['tipo']);
            generarFormulario($tipo, $error, "", "");
        }elseif($_SERVER["REQUEST_METHOD"] == "POST"){ // Validar o repintar formulario si no es valido
            $matricula = trim(htmlspecialchars($_POST['matricula']));
            $dato = trim(htmlspecialchars($_POST['dato']));
            $error = validarFormulario($error, $tipo, $matricula ?? "", $dato ?? "", $archivo);
            // Verifico los errores que encontrados y si no ingreso el permiso
            if (contarErrores($error) === 0) {
                ingresarPermisos($archivo, $tipo, $matricula, $dato);
            }else{
                generarFormulario($tipo, $error ?? "", $matricula ?? "", $dato ?? "");
            }
        }

        // Generar el formulario con errores si es que los detecta
        function generarFormulario($tipo, $error, $matricula, $dato){
            if ($tipo !== null || $tipo !== "" || $tipo !== " ") { // Valido por si el dato tipo a tenido algun error
                echo '<input type="hidden" name="tipo" value='.$tipo.' checked>
                <h1>' . ucfirst($tipo) . '</h1>';// Para saber el tipo del formulario al repintar
                echo '<label for="matricula">Ingrese la matricula</label>
                <input type="text" name="matricula" value="'.$matricula.'">'.$error['matricula'];
                switch ($tipo) {
                    case 'taxis':
                        echo '<label for="nombre">Ingrese el nombre del propietario</label>';
                        break;
                    case 'EMT':
                        echo '<label for="nombre">Ingrese la direccion</label>';
                        break;
                    case 'servicios':
                        echo '<label for="nombre">Ingrese que servicio es</label>';
                        break;
                    case 'residentesYHoteles':
                        echo '<label for="nombre">Ingrese la direccion</label>
                        <input type="text" name="dato" value="'.$dato.'"><br>'.$error['dato'].'
                        <label for="fecha_ini">Introduzca la fecha de inicio de permiso</label> <br>
                        <input type="date" name="fecha_ini">
                        <label for="fecha_fin">Introduzca la fecha de fin de permiso</label> <br>
                        <input type="date" name="fecha_fin"> <br>'.$error['fecha'].'<br>
                        ';
                        break;
                    case 'logistica':
                        echo '<label for="nombre">Ingrese la empresa</label>';
                        break;
                    default:
                        echo 'Ha ocurrido un error al elegir el tipo
                        <a href="formularioMovilidad.html">Volver</a>';
                        break;
                }
                if ($tipo !== 'residentesYHoteles') {
                    echo '<input type="text" name="dato" value="'.$dato.'">'.$error['dato'];
                }
                echo '<br><input type="submit" value="Enviar"> <br>
                <br> <a href="formularioMovilidad.html">Volver</a>';
            }else {
                echo 'Ha ocurrido un error al elegir el tipo de permiso <br>
                <a href="formularioMovilidad.html">Volver</a>';
            }
        }

        // Verificar la entrada de permisos
        function validarFormulario($error, $tipo, $matricula, $dato, $archivo){
            if ($matricula === "") {
                $error['matricula'] .= '<span class="error"> La matricula es obligatoria</span><br>';
            }
            if ($dato === "") {
                $error['dato'] .= '<span class="error"> El dato es obligatoria</span><br>';
            }
            if ($tipo === "residentesYHoteles") {
                $error['fecha'] .= validarRangoFechas();
            }
            if (buscarMatricula($archivo,$matricula)){
                $error['matricula'].= '<span class="error"> Esta matricula ya esta registrada</span><br>';
            }
            return $error;
        }

        // Verificar que la matricula no este ya en el txt
        function buscarMatricula($archivo,$matricula){
            $puntero = fopen($archivo, 'r');
            if ($puntero) {
                while (!feof($puntero)) {
                    $linea = fgets($puntero);
                    $linea = trim($linea);
                    $vehiculo = explode(" ", $linea);
                    if ($vehiculo[0] == $matricula && $vehiculo[0] != "") {
                        return true;
                    }
                }
                fclose($puntero);
            } else {
                echo "Error al abrir el archivo en buscarMatricula<br>";
                die();
            }
            return false;
        }

        function validarRangoFechas(){
            $fecha_ini = htmlspecialchars($_POST['fecha_ini']);
            $fecha_fin = htmlspecialchars($_POST['fecha_fin']);
            if ($fecha_ini === null || $fecha_fin === null) {
                return '<br> <span class="error">Las fechas no pueden ser null</span><br>';
            }
            $fecha_ini = strtotime($fecha_ini);
            $fecha_fin = strtotime($fecha_fin);
            if ($fecha_ini < $fecha_fin) {
                return "";
            }else {
                return '<br> <span class="error">La fecha de inicio debe ser menor a la final</span><br>';
            }
        }

        // Funcion para saber si el formulario no contiene error
        function contarErrores($error){
            $contador = 0;
            foreach ($error as $value) {
                if ($value != ""){
                    $contador ++;
                }
            }
            return $contador;
        }

        // Una vez valido los campos lo ingreso en los txt despues de dar x formato
        function ingresarPermisos($archivo, $tipo, $matricula, $dato){
            $linea = "";
            if ($tipo === "residentesYHoteles") {
                $fecha_ini = htmlspecialchars($_POST['fecha_ini']);
                $fecha_fin = htmlspecialchars($_POST['fecha_fin']);
                // Para ingresarlo al txt y que no tenga fallos al ingresar espacios los quito y colo formato '/' A la fecha
                $linea = darFormato($tipo, $matricula,$dato, $fecha_ini, $fecha_fin);
            }else {
                $linea = darFormato($tipo, $matricula,$dato, "", "");
            }
            if (escribirEnTxt($archivo, $linea)) {
                echo 'usuario ingresado <br>Datos ingresados: <br>';
                echo $archivo." ".$tipo." ".$matricula." ".$dato;
                echo '<br><br> <a href="formularioMovilidad.html">Volver</a>';
            }
        }
        
        // Formatos para el txt
        function darFormato($tipo, $matricula,$dato, $fecha_ini, $fecha_fin){
            $matricula = quitarEspacios($matricula);
            $dato = quitarEspacios($dato);
            if ($tipo === 'residentesYHoteles') {
                $fecha_ini = darFormatoFecha($fecha_ini);
                $fecha_fin = darFormatoFecha($fecha_fin);
            }
            return $matricula." ".$dato." ".$fecha_ini." ".$fecha_fin;
        }

        function darFormatoFecha($fecha){
            $fecha = explode("-",$fecha);
            return $fecha[0].'/'.$fecha[1].'/'.$fecha[2];
        }

        function quitarEspacios($cadena){
            $auxiliar = "";
            $auxiliar = explode(" ", $cadena);
            $cadena = "";
            foreach ($auxiliar as $value) {
                $cadena .= $value;
            }
            return $cadena;
        }

        // Ingresar el dato, cuidado por que suele no poder al fichero al no tener permisos se debe cambair los permisos de la carpeta de otros
        // para lectura y ecritura
        function escribirEnTxt($archivo, $linea){
            $txt = fopen($archivo, "a");// En este modo se supone que me lo insertal al fianl del txt
            if ($txt) {
                fwrite($txt, PHP_EOL.$linea);
                fclose($txt);
                return true;
            } else {
                echo "No se pudo abrir el archivo.De la ruta ".$archivo.' Revise los permisos de los otros usuarios<br>';
                echo '<br> <a href="formularioMovilidad.html">Volver</a>';
                return false;
            }
        }
        ?>
    </form>
</body>
</html>