<?php
	class AsignacionCompleta {
		private $listaAsignaciones;
		private $necesidades;


		public function AsignacionCompleta( $n ) {
			$this->listaAsignaciones = array();
			$this->necesidades = $n;
		}

		/**
		* Este metodo se encarga de mutar a una asignacion completa
		* 
		*/
		function mutar() {
			$asignacionIndividual1 = mt_rand( 0, count($this->listaAsignaciones)-1 );
			$asignacionIndividual2 = mt_rand( 0, count($this->listaAsignaciones)-1 );
			$recurso = $this->listaAsignaciones[$asignacionIndividual2]->getRecursoAleatorio();
			if( $recurso != false) {
				$this->listaAsignaciones[$asignacionIndividual1]->addRecurso( $recurso );
			}
			
		}

		function addAsignacion( $asignacion ) {
			array_push( $this->listaAsignaciones, $asignacion );
		}

		function getFitness() {
			$aptitud = 0;
			$i = 0;
			foreach ($this->listaAsignaciones as $asignacionIndividual) {
				$aptitud += $asignacionIndividual->getFitness( $this->necesidades[ $i++ ] );
			}
			return $aptitud;
		}

		function addAsignacionAt( $asignacion , $index ) {
			$this->listaAsignaciones[ $index ] = $asignacion;
		}

		function getAsignaciones() {
			return $this->listaAsignaciones;
		}

		function getNumeroAsignaciones() {
			return count( $this->listaAsignaciones );
		}

		function getTotalRecursos() {
			$total = 0;
			foreach ($this->listaAsignaciones as $asignacion) {
				$total += $asignacion->getTotalRecursos();
			}
			return $total;
		}

		function getElementAt( $index ) {
			return $this->listaAsignaciones[ $index ];
		}
	}
?>