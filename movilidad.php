<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <style>
        .contenedor {
            background-color: white;
            box-shadow: inset 2px;
            margin: auto;
            width: 60%;
            border-radius: 20px;
            text-align: center;
            padding: 15px;
        }
        a{
            text-decoration: none;
            color: white;
            padding: 5px;
            border-radius: 2px;
            background-color: #4CAF50;

        }
    </style>
    <div class="contenedor">
        <h1>Vehiculos infractores</h1>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            generarInfractores();
        }
        
        function generarInfractores(){
            $archivo = 'datos/vehiculos.txt';
            $archivos = array('taxis' => 'datos/taxis.txt', 'EMT' => 'datos/vehiculosEMT.txt', 'servicios' => 'datos/servicios.txt', 'residentesYHoteles' => 'datos/residentesYHoteles.txt', 'logistica' => 'datos/logistica.txt');
            $punteros = crearPunteros($archivos);
            leerVehiculos($archivo, $punteros);
            cerrarPunteros($punteros);
        }

        function crearPunteros($archivos){
            $punteros = [];
            foreach ($archivos as $key => $archivo) {
                $punteros[$key] = fopen($archivo, 'r');
            }
            return $punteros;
        }

        // Se usa al terminar para cerrar ficheros
        function cerrarPunteros($punteros){
            foreach ($punteros as $puntero) {
                fclose($puntero);
            }
        }

        // Funcion que va linea a linea comprobando si tiene permisos y si no los pinta
        function leerVehiculos($archivo, $punteros){
            $txt = fopen($archivo, 'r');
            if ($txt) {
                while (!feof($txt)) {
                    $linea = fgets($txt);
                    $linea = trim($linea);
                    if (!buscarEnPunteros($linea, $punteros)){
                        echo $linea.'<br>';
                    }
                }
                fclose($txt);
            } else {
                echo "Error al abrir el archivo";
                die();
            }
        }

        function buscarEnPunteros($linea,$punteros){
            $arrayAuxiliar = explode(" ", $linea);
            $vehiculo = array(
                "matricula" => $arrayAuxiliar[0], "titular" => $arrayAuxiliar[1],
                "direccion" => $arrayAuxiliar[2], "fecha" => $arrayAuxiliar[3],
                "hora" => $arrayAuxiliar[4], "tipo" => $arrayAuxiliar[5]
            );
            if ($vehiculo["tipo"] === "electrico" || $vehiculo["tipo"] === "electríco"){
                return true;
            }
            foreach ($punteros as $key => $puntero) {
                if ($puntero) {
                    rewind($puntero); // importante para reiniciar el puntero del fichero
                    if ($key === 'residentesYHoteles' || $key === 'logistica') {
                        if (tipoEspecial($vehiculo, $key, $puntero)) { // Validar para casos especificos residentes/logistica
                            return true;
                        }
                    }elseif(buscarMatricula($puntero, $vehiculo["matricula"])){
                        return true;
                    }
                }else{
                    echo 'Error al abrir fichero';
                    die();
                }
            }
        }

        // Comprobar si esta en x txt de tipo especial
        function tipoEspecial($vehiculo, $key, $puntero){
            if ($key === 'residentesYHoteles'){
                return esResiHotel($vehiculo['matricula'], $vehiculo['fecha'] ,$puntero);
            }elseif ($key === 'logistica') {
                return esLogistica($vehiculo['matricula'], $vehiculo['hora'], $puntero);
            }
        }

        function buscarMatricula($puntero,$matricula){
            if ($puntero) {
                rewind($puntero);
                while (!feof($puntero)) {
                    $linea = fgets($puntero);
                    $linea = trim($linea);
                    $vehiculo = explode(" ", $linea);
                    if ($vehiculo[0] == $matricula) {
                        return true;
                    }
                }
            } else {
                echo "Error al abrir el archivo en buscarMatricula<br>";
                die();
            }
            return false;
        }

        // Funcion que valida la hora especifica en el rango prestablesido
         function esLogistica($matricula, $hora, $puntero){
            if (buscarMatricula($puntero,$matricula)){
                $hora_inicio = '06:00'; // hora de inicio del rango
                $hora_fin = '11:00'; // hora de fin del rango
                $hora_inicio = strtotime( $hora_inicio);
                $hora_fin = strtotime($hora_fin);
                $hora = strtotime($hora);
                return $hora >= $hora_inicio && $hora <= $hora_fin;
            }
            return false;
        }

        function esResiHotel($matricula, $fecha,$puntero) {
            if ($puntero) {
                rewind($puntero);
                while (!feof($puntero)) {
                    $linea = fgets($puntero);
                    $linea = trim($linea);
                    $auxiliar = explode(" ", $linea);
                    $vehiculoTxt = array('matricula' => $auxiliar[0], 'fecha_iniTxt' => $auxiliar[2], 'fecha_finTxt' => $auxiliar[3]);
                    if ($vehiculoTxt['matricula'] == $matricula) {
                        return enRangoFecha($fecha, $vehiculoTxt['fecha_iniTxt'], $vehiculoTxt['fecha_finTxt']);
                    }
                }
            } else {
                echo "Error al abrir el archivo en buscarMatricula la ruta es de residentes <br>";
            }
            return false;
        }

        function enRangoFecha($fecha, $fecha_iniTxt, $fecha_finTxt){
            $fecha = strtotime($fecha);
            $fecha_iniTxt = strtotime($fecha_iniTxt);
            $fecha_finTxt = strtotime($fecha_finTxt);
            return $fecha >= $fecha_iniTxt && $fecha <= $fecha_finTxt;
        }
        ?>
        <br>
        <a href="formularioMovilidad.html">Volver</a>
    </div>
</body>
</html>