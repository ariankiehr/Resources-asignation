
<?php

	/**
	* Esta clase guarda un recurso particular
	* el recurso tiene nombre y un valor que indica cuan importante es darlo
	* ademas posee un contador que indica la cantidad de recursos de ese tipo 
	* con lo que se cuenta.
	*/

	class Recurso {

		private $nombreRecurso;
		private $valorAsociado;
		private $cantidad;


		function Recurso( $nombre, $valor ) {
			$this->nombreRecurso = $nombre;
			$this->valorAsociado = $valor;
			$this->cantidad = 1;
		}

		function agregarUnidad() {
			$this->cantidad++;
		}
	
		function decrementarUnidad() {
			if($this->cantidad == 1 ) {
				return false;
			}
			$this->cantidad--;
			return true;
		}

		function getNombre() {
			return $this->nombreRecurso;
		}		

		function getValor() {
			return $this->valorAsociado;
		}

		function getCantidad() {
			return $this->cantidad;
		}
	}
?>