<?php
	/**
	* Esta funcion representa el principal operador genetico del algoritmo, la recombinacion.
	* Dados dos ciudadanos (representados por un tipo de dato AsignacionCompleta) genera una nueva
	* AsignacionCompleta conocida como hijo o nueva generacion.
	*---------------------------------------------------------------------------------------------
	* Recibe como parametro dos signacionesCompletas que son los padres
	* Una lista de recursos en donde hay recursos repetidos con cantidad 1
	* Es una AsignacionPorPersona en donde se guardan todos los recursos (el stock)
	* pasada a lista usando el metodo de la clase AsignacionPorPersona, getRecursosComoLista()
	* El ultimo parametro es la cantidad de personas que estan pidiendo por los recursos, integer.
	*---------------------------------------------------------------------------------------------
	* El algoritmo utilizado para hacer la recombinacion fue una variacion de 'Cut and Slice'
	* Se crea una AsignacionCompleta vacia que va a ser el valor de retorno, y luego se elije
	* un lugar aleatorio por el que cortar los padres, la mitad derecha del primero mas la mitad
	* izquierda del segundo forman al numero hijo; de esta menera si se tiene:
	* ( 1, 2, 3, 4 ) y ( 4, 3, 2, 1 ) 
	* como los dos padres donde cada numero representa cantidad de recursos y el aleatorio es 2
	* de esta forma el hijo seria:
	* ( 4, 3, 3, 4 )
	* Se puede generar de forma facil el gemelo (las mitades restantes) pero no se hace.
	* La primer variacion que se presenta es que el hijo generado sin ningun control puede exeder
	* la cantidad de recursos que hay en el stock, como sucedio en el ejemplo de arriba,
	* que solo habia 10 recursos, pero el hijo tiene 14.
	* Para solucionar este problema lo que se hace es asinar de a uno por ves intercalado desde el
	* punto de partida, por ejemplo, si el hijo en primera instancia esta vacio seria:
	* ( 0, 0, 0, 0 ) ->  ( 0, 0, 3, 0 ) -> ( 0, 3, 3, 0 ) -> ( 0, 3, 3, 4 ) -> ( 4, 3, 3, 4 )
	* Cada ves que se hace una asignacion se verifica el stock a ver si existen los productos
	* que se quieren asignar, en el caso de arriba la ultima asignacion no se hubiera hecho
	* dado que ( 0, 3, 3, 4 ) ya alcanzo los 10 recursos asignados y terminaria el algoritmo.
	* El problema sobre esto es que trata los recursos como todos diferentes pero no lo son
	* el algoritmo anterior asigno 10 recurosos, pero puede haber asignado 10 pq de harina cuando
	* solo habia 5 y lo restante era azucar, por ejemplo.
	* Para solucionar este problema se una la funcion obtenerMaximosRecursos que por cada
	* Asignacion individual devuelve la cantidad maxima de recursos que se puede asignar 
	* dado el stock remanente, de esta forma la asignacion puede ser de esta forma
	* ( 0, 0, 0, 0 ) ->  ( 0, 0, 3, 0 ) -> ( 0, 2, 3, 0 ) -> ( 0, 2, 3, 3 ) -> ( 1, 2, 3, 3 )
	* En este caso la primer asignacion se hizo bien (siempre es asi ya que el padre es correcto)
	* La segunda asignacion perdio un elemento porque con haber asignado los primero 3 ya 
	* se agoto en el stock, lo mismo pasa con la tercera, y en la cuarta se puede asignar solo uno
	* Este elgorimo soluciona el problema pero queda por solucionar (como en el caso de arriba)
	* Que pueden quedar recursos sin asignar, dado que se asignan una mitad de cada padre
	* si en las mitades que no se usan esta por ejemplo toda la harina, el hijo se queda sin harina
	* por eso una ves terminada de hacer una asignacion, se recorre lo que sobre de los recursos
	* y se asignana aleatoriamente a as personas hasta agotar el stock
	*/

	function crossover( $ciudadano1, $ciudadano2, $listaRecursos, $nroNecesitados, $necesidades ) {
		$hijo = new AsignacionCompleta( $necesidades );
		$puntoParticionCreciente = mt_rand( 0, $nroNecesitados-1 );
		$puntoParticionDecreciente = $puntoParticionCreciente - 1; 
		$x = array();
		while ( $puntoParticionCreciente < $nroNecesitados || $puntoParticionDecreciente >= 0 ) {

		  if( $puntoParticionCreciente < $nroNecesitados ) {
		    $asignacionIndividual = $ciudadano1->getElementAt($puntoParticionCreciente)->getRecursosComoLista();
		    $asig = obtenerMaximosRecursos( $asignacionIndividual , $listaRecursos);
		    $x[$puntoParticionCreciente] = $asig;
		    $puntoParticionCreciente++;
		  }

		  if( $puntoParticionDecreciente >= 0 ) {
		    $asignacionIndividual = $ciudadano2->getElementAt($puntoParticionDecreciente)->getRecursosComoLista();
		    $asig = obtenerMaximosRecursos( $asignacionIndividual , $listaRecursos);
		    $x[$puntoParticionDecreciente] = $asig;
		    $puntoParticionDecreciente--;
		  }

		}

		//Se asignana los recurosos que faltan
		foreach ( $listaRecursos as $recurso ) {
		  $random = mt_rand(0, $nroNecesitados-1 );
		  $x[ $random ]->addRecurso( $recurso );
		}
		//se ordena al hijo de acuerdo a los padres y se produce la asignacion
		ksort($x);
		foreach ($x as $value) {
		  $hijo->addAsignacion( $value );
		}

		return $hijo;
	}

	/**
	* Esta funcion recibe dos listas, una con los recursos que se quieren asignar
	* y otra con los recursos disponibles, devuelve una AsignacionPorPersona
	* con la cantidad maxima de recursos que se pueden aignar y modifica la lista
	* que contiene el stock con todo lo que se quito
	*/
	function obtenerMaximosRecursos( $seQuierenAsignar, &$seTienenParaAsignar ) {
		$maximaAsignacionPersona = new AsignacionPorPersona();

		foreach ($seQuierenAsignar as $recurso) {
		  $key = array_search( $recurso, $seTienenParaAsignar );
		  if( $key !== false ){
		    $maximaAsignacionPersona->addRecurso( $recurso );
		    unset( $seTienenParaAsignar[$key] );
		  }
		}
		return $maximaAsignacionPersona;
	}


?>