<?php
  include "conec.php";
  include "Recurso.php";
  include "AsignacionPorPersona.php";
  include "AsignacionCompleta.php";
  include "generadorDeCiudadano.php";
  include "crossover.php";


  /**
   * Dada la consulta SQL sobre la tabla de recursos esta funcion devuelve
   * los datos en una estructura de AsignacionPorPersona
   */
  function getRecursos( $cantidadRecursos, $tablaRecursosSQL ) {
    $recursos =  new AsignacionPorPersona();

    for( $i = 0; $i < $cantidadRecursos; $i++ ) {
      $nombreRecurso = pg_result( $tablaRecursosSQL, $i, 1 );
      $valorRecurso = pg_result( $tablaRecursosSQL, $i, 2 );
      $recursos->addRecurso( new Recurso( $nombreRecurso, $valorRecurso ) );
    }

    return $recursos;
  }

  function getNombres( $nroNecesitados, $QueryNecesitados ) {
    $nombres = array();
    for( $i = 0; $i < $nroNecesitados; $i++ ) {
      $nombre = pg_result( $QueryNecesitados, $i, 0 );
      $nombres[ $i ] = $nombre;
    }
    return $nombres;
  }


  function getNecesidades( $nroNecesitados, $QueryNecesitados ) {
    $necesidades = array();
    $total = 0;
    for( $i = 0; $i < $nroNecesitados; $i++ ) {
      $nec = pg_result( $QueryNecesitados, $i, 1 );
      $total += $nec;
      $necesidades[ $i ] = $nec;
    }

    for ( $i=0; $i< $nroNecesitados; $i++) {
      $necesidades[$i] =  ($necesidades[$i] / $total);
    }
    return $necesidades;
  }


  /**
  * Funcion que ordena a la poblacion de acuerdo a su aptitud de asignacion
  */
  function osort( &$array ) {
    usort($array, function($a, $b) {
      return ( $a->getFitness() > $b->getFitness() ) ? 1 : -1;
    });
  }

  /** Creacion de una tabla, mas que nada para debug */
  function crearTabla( $nombres , $necesidades ) {
    //Para que la tabla se adecue a la longitud de los nombre
    echo "<style type=\"text/css\">
        ul {
        padding: 0;
        margin: 0;
        list-style:square;
        }
        li {
        margin-left: 15px;
        }
        </style>";

    echo "<table border=\"1\">";

    //Direcciones
    echo "<tr>";
    foreach ($nombres as $nombre) {
      echo "<th>$nombre</th>";
    }
    echo "</tr>";

  }

  function finTabla() {
    echo"</table>";
  }

  function mostrarPoblacion( $poblacion ) {
    for ( $i = 0; $i < 1; $i++ ) {
      echo "<tr>";
      $asignacionCompleta = $poblacion[$i];

      foreach ( $asignacionCompleta->getAsignaciones() as $asignacionIndividual ) {
        echo "<td>";
        $recursos = $asignacionIndividual->getRecursosComoLista();
        foreach ($recursos as $recurso) {
          $nombre = $recurso->getNombre();
          echo "<ul><li>$nombre</li></ul>";
        }
        echo "</td>";
      }
      echo "</tr>";
    }
  }

  /**
  * Flags para que el navegador espere a el programa que carge
  */
  set_time_limit(0);
  ignore_user_abort(true);
  while( ob_get_level() ) ob_end_clean();
  ob_implicit_flush(true);

  /** Conexion con la DB*/
  $con = pg_connect ( $strCnx ) or die ( "Error de conexion.". pg_last_error() );


  /** Tabla recursos*/
  $tablaRecursosSQL = pg_query( $con, "
    SELECT id, nombre, valor FROM recurso
  " );

  /** Las personas, para saber  */
  $QueryNecesitados = pg_query( $con, "
    SELECT dir,( ( puntajeSocial + escuela + ocup )/3 )::float AS necesidad
  FROM (SELECT direcciones.direction_id AS dir, COALESCE( escolaridad.promedio, 10 ) AS escuela
    FROM (
      SELECT sq.direction_id
      FROM(SELECT direction_id
          FROM people
          GROUP BY direction_id
        ) AS sq
        INNER JOIN people p ON (sq.direction_id = p.direction_id)
      GROUP BY sq.direction_id
      ) AS direcciones
    LEFT JOIN (
        SELECT  direction_id, AVG(e.opcion) AS promedio
        FROM rkn_encues_rpta e INNER JOIN people p ON (e.id_persona = p.id) INNER JOIN rkn_preguntas pr ON (pr.pregunta = e.pregunta)
        WHERE (e.pregunta = 55)
        GROUP BY  direction_id) AS escolaridad ON (escolaridad.direction_id = direcciones.direction_id)
    ) AS c2
  INNER JOIN (
    SELECT DISTINCT sq.direction_id AS direccion, COALESCE (sq2.opcion,10) AS puntajeSocial
    FROM (
      (SELECT  direction_id FROM  people p ) AS sq
      LEFT JOIN (
        SELECT direction_id, opcion
        FROM rkn_encues_rpta e INNER JOIN people p ON (p.id = e.id_persona)
        WHERE pregunta = 75)AS sq2 ON (sq.direction_id = sq2.direction_id))
    ) AS c1 ON (c1.direccion = c2.dir)
  INNER JOIN (
    SELECT Fam.direction_id AS dire, COALESCE(9*(1-(desocupados/integrantes::float)),10) AS ocup
    FROM (  SELECT direction_id, count(*) AS integrantes FROM people GROUP BY direction_id ) AS Fam
    LEFT JOIN (
      SELECT Pers.direction_id, count(*) AS desocupados
      FROM rkn_encues_rpta Rta
      LEFT JOIN people Pers ON ( Rta.id_persona = Pers.id )
      WHERE pregunta = 56 AND Rta.opcion NOT IN ( 1, 2, 6, 7, 8, 9, 10 )  -- sin ocupacion
      GROUP BY Pers.direction_id
      ) AS Des ON ( Fam.direction_id = Des.direction_id ))
    AS c3 ON ( c3.dire = c1.direccion );
  " );

  $nroCanastasSQL = pg_query( $con, "
    SELECT MIN(c)
      FROM (
       SELECT COUNT(*) AS c
       FROM recurso
       WHERE (nombre IN ('harina', 'azucar', 'fideos', 'salsa tomate', 'aceite', 'arroz'))
       GROUP BY nombre
    ) AS sq;
  ");

  /** Constantes */
  $cantidadRecursos =  pg_numrows( $tablaRecursosSQL );
  $nroNecesitados =  pg_numrows( $QueryNecesitados );
  $nroCanastas = pg_result( $nroCanastasSQL, 0, 0 );
  $nombres = getNombres( $nroNecesitados, $QueryNecesitados );
  $necesidades = getNecesidades( $nroNecesitados, $QueryNecesitados );
  array_multisort( $necesidades, $nombres ); //oredena por necesidad y ordena tambien el de nombres par que queden con quien corresponde
  $maxPoblacion = 500;
  $elite = 100; //tiene que ser la mitad de $maxPoblacion paa quedarse con la mitad de mejores
  $maxGeneraciones = (int)htmlspecialchars($_GET["profundidad"]); // no siempre se llega, puede converger antes
  $nivelDeConvergencia = 3;
  //ob_start(); //para eliminar las salidas de debug

  //500 Poblacion, 100 elite, 5 generaciones, 450 recursos, 3 nivel convergencia -> 1:30 minutos

  $recursos = getRecursos( $cantidadRecursos, $tablaRecursosSQL );
  $recursos->generarCanastas( $nroCanastas );

  pg_FreeResult( $tablaRecursosSQL );
  pg_FreeResult( $QueryNecesitados );
  pg_FreeResult( $nroCanastasSQL );

  $poblacion = array();
  $mejorAptitud = 0;
  $converge = 0;

  /*Para asignar los recursos con probabilidades en la primera genracion*/
  $acum = array();
  $total = 0;
  foreach ($necesidades as $necesidad) {
      $valor = (0.02 - $necesidad)*10000;
      $total += $valor;
      array_push( $acum, $total );
  }

  //Se crea la poblacion inicial
  for ( $ciudadano = 0; $ciudadano < $maxPoblacion; $ciudadano++ ) {
    $listaRecursos = $recursos->getRecursosComoLista();
    $poblacion[ $ciudadano ] = crearPoblacionAleatoria( $nroNecesitados, $listaRecursos, $necesidades, $acum, $total );
  }

  //Se crean la generaciones
  for ( $gen = 0; $gen < $maxGeneraciones; $gen++ ) {
    osort( $poblacion );

    $temporal = $poblacion[0]->getFitness();
    if( $mejorAptitud == $temporal ) {
      $converge++;
    } else {
      $converge = 0;
    }
    $mejorAptitud = $temporal;

    if( $converge == $nivelDeConvergencia ) {
      break;
    }


    //se crea todo la nueva generacion
    //copio el arreglo a partir del elitismo pra conservar los mejores especimenes
    for( $ciu = $elite; $ciu < $maxPoblacion; $ciu++ ) {
      $listaRecursos = $recursos->getRecursosComoLista();
      $padre1 = mt_rand( 0 , $elite - 1 );
      $padre2 = mt_rand( 0 , $elite - 1 );
      $poblacion[ $ciu ] = crossover( $poblacion[ $padre1 ], $poblacion[ $padre2 ] , $listaRecursos, $nroNecesitados, $necesidades );
      if ( mt_rand( 0, 1 ) < 0.3 ) {
        $poblacion[ $ciu ]->mutar();
      }
    }
  }

  osort( $poblacion );
  //ob_end_clean();
  crearTabla( $nombres , $necesidades ); //genera una tabla html para debug
  mostrarPoblacion( $poblacion );
  unset( $poblacion );


  finTabla(); //cierra la tabla, debug

  pg_close ($con);
?>
