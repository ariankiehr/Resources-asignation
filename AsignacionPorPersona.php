<?php

	/**
	* Esta clase almacena una lista de clase Recursos correspondientes a lo que se le va a dar
	* a una persona, mantiene los recursos agrupados (un unico recusos con un contador)
	*/
	class AsignacionPorPersona {
		private $listaRecursos;
		private $aptitud;

		public function AsignacionPorPersona() {
			$this->listaRecursos = array();
			$this->aptitud = -1;
		}

		/* Para Mutar */
		function getRecursoAleatorio() {
			if( count($this->listaRecursos) > 0 ) {

				$recurso = mt_rand( 0, count($this->listaRecursos)-1 );
				if ( true == $this->listaRecursos[$recurso]->decrementarUnidad()) {
					return ( new Recurso( $this->listaRecursos[$recurso]->getNombre()  , $this->listaRecursos[$recurso]->getValor() ) );
				}
				$rec = $this->listaRecursos[ $recurso ];
				unset( $this->listaRecursos[ $recurso ] );
				$this->listaRecursos = array_values( $this->listaRecursos );
				$this->aptitud = -1;
				return $rec;
			}
			return false;
		}


		function getPosicionRecurso( $recurso ) {
			for( $i = 0; $i < count( $this->listaRecursos ); $i++ ) {
				if( $this->listaRecursos[$i]->getNombre() == $recurso->getNombre()  ) {
					return $i;
				}
			}
			return -1;
		}

		function getRecursos() {
			return $this->listaRecursos;
		}

		function getRecursosComoLista() {
			$recursos = array();
			foreach ($this->listaRecursos as $recurso) {
				for( $i = 0; $i < $recurso->getCantidad(); $i++ ) {
					array_push($recursos, new Recurso( $recurso->getNombre(), $recurso->getValor() ) );
				}
			}
			return $recursos;
		}


		function generarCanastas( $nroVeces ) {
			foreach ($this->listaRecursos as $recurso) {
		      if ( $recurso->getNombre() == "harina" ||
		           $recurso->getNombre() == "azucar" ||
		           $recurso->getNombre() == "fideos" ||
		           $recurso->getNombre() == "salsa tomate" ||
		           $recurso->getNombre() == "aceite" ||
		           $recurso->getNombre() == "arroz" ) {
		        for ( $i = 0; $i < $nroVeces ; $i++) { 
		         if( $recurso->decrementarUnidad() == false ){
		    			$pos = $this->getPosicionRecurso( $recurso );
		    			unset( $this->listaRecursos[$pos] );
		    			$this->listaRecursos = array_values($this->listaRecursos);
		    		}
		        }
		      }
		    }

		    for ( $i = 0; $i < $nroVeces; $i++ ) { 
		    	$this->addRecurso( new Recurso( "Kit Basico" , 20 ) );
		    }

		}		

		function addRecurso( $recurso ) {
			$this->aptitud = -1;
			$posicion = $this->getPosicionRecurso( $recurso );
			if( $posicion == -1 ) {
				array_push( $this->listaRecursos, $recurso );
			} else {
				$this->listaRecursos[$posicion]->agregarUnidad();
			}
		}



		/**
		* Esta funcion dada una AsignacionPorPersona devuelve un indicardor de que
		* tan buena es esa asignacion (basandose en factores como la cantidad y variedad)
		* El indicador actual que se usa esta hecho medio a la rapida
		* A medida que se asignan recursos iguales bajan el valor que representan por ejemplo
		* 1 harina que vale 3 devuelve : 
		*/
		function getAptitud() {
		    $indicadorAsignacion = 0;
		    foreach ($this->listaRecursos as $recurso) {
		    	$repetidos = (($recurso->getCantidad() * $recurso->getValor()) * 1.5) -  ($recurso->getValor() * 1.5);
		      	$indicadorAsignacion += ( $recurso->getValor() + $repetidos ) ;
		    }
		    return $indicadorAsignacion;
		    //return $this->getTotalRecursos();
		}

		/**
		* Esa funcion devuelve un indicardor de que tan buena es la asignacion, en base
		* a la necesidad de la persona a la que se le asigna	
		*/
		function getFitness( $necesidad ) {
			if( $this->aptitud != -1 ) {
				return $this->aptitud;
			}
    		$asignacion = $this->getAptitud();
    		if ( $asignacion == 0 ) {
    			return 0;
    		}
    		$this->aptitud = $necesidad * $asignacion;
    		return $this->aptitud ;
  		}


		function getDiferentesRecursos() {
			return count( $this->listaRecursos );
		}

		function getTotalRecursos() {
			$total = 0;
			foreach ($this->listaRecursos as $recurso) {
				$total += $recurso->getCantidad();
			}
			return $total;
		}

	}

?>