<?php

    /**
    * Esta funcion crea una AsignacionCompleta aleatoria enbase a los
    * recursos que haya en un stock asignando la totalidad de los
    * recursos aleatoriamente a las personas.
    */
   function crearPoblacionAleatoria( $nroNecesitados, $recursos, $necesidades, $acum, $total ) {
    $asignacionCompleta = new AsignacionCompleta( $necesidades );

    for ( $p = 0; $p < $nroNecesitados; $p++ ) { 
    	$asignacionCompleta->addAsignacion( new AsignacionPorPersona() );
    }
    
    for ( $i = 0; $i < count($recursos); $i++ ) {
        $random = mt_rand( 0, $total );        
        $personaRandom = 0;
        $t = $nroNecesitados-1;
        while( $personaRandom < $t && $acum[ $personaRandom ] < $random  ) {
            $personaRandom ++;
        }
        $asignacionCompleta->getElementAt( $personaRandom )->addRecurso( $recursos[$i] );
    }

    return $asignacionCompleta;
  }
?>