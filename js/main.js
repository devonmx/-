$(document).ready(function(){
	// Declaramos variables Globales con las que se trabajaran
	var salarios, equiposMetas;

	// Filtros de la tabla
	$('table').tablesorter();

	// Carga de la información de archivos
	$('#file-1, #file-2').change(function(event){
		// Creamos las variables que ocuparemos
		var reader = new FileReader(), obj, im = $(this).data("name");
		// Lectura de los archivos.
		reader.onload = function(event){
			try {
				obj = JSON.parse(event.target.result);
				// Asignamos a las variables globales dependiento que input sea para asignar la información a calcular
				if(im == "salarios"){
					salarios = obj;
				}else{
					equiposMetas = obj;
				}

				// Arrojamos un mensaje de Exito
				Swal.fire({
					position: 'center',
					type: 'success',
					title: 'Se cargo el archivo correctamente',
					showConfirmButton: false,
					timer: 1500
				})
			} catch (ex) {
				// Caso contrario que no sea un formato correcto lo bateamos
				Swal.fire({
					type: 'error',
					title: 'Formato invalido',
					text: 'Ingrese el formato correcto, para procesar los calculos adecuadamente.',
				})
			}
		};
		reader.readAsText(event.target.files[0]);
	});

	// Calculamos todo el relajo
	$('#calcular').click(function(){
		$('#ajaxBusy').fadeIn(300);
		//Declaramos Variables
		var alcanceGrupal = 0, alcanceIndividual = 0;
		// Recorremos los datos que nos envian para calcular información Global
		// Primer Recorrido del MAP agrega información para las Metas Grupales en EQUIPOS y Agrega metas individuales en SALARIOS
		$.map(salarios, function(jugador, index){
			// Validación arcaica si existe el valor en caso contrario tira el proceso
			if(!equiposMetas[jugador.equipo]){
				Swal.fire({
					type: 'error',
					title: 'Error al procesar',
					text: 'Verifique que sus archivos tengan el formato solicitado para procesar su información correctamente.',
				})
				return false;
			}

			// Agrega la Meta Minimo de Goles Individual y las indexa en el array de Salarios
			minimoGoles = equiposMetas[jugador.equipo][jugador.nivel];
			jugador.goles_minimos = minimoGoles;

			// Agrega la Meta Minima de Goles que deben anotar por Equipo en el array de equiposMetas
			(equiposMetas[jugador.equipo]['minimo'] != undefined) ? (equiposMetas[jugador.equipo]['minimo'] +=  minimoGoles) : (equiposMetas[jugador.equipo]['minimo'] =  minimoGoles);

			// Global de Goles Hechos por equipo y las indexa en el array de equiposMetas
			(equiposMetas[jugador.equipo]['hechos'] != undefined) ? (equiposMetas[jugador.equipo]['hechos'] += jugador.goles) : (equiposMetas[jugador.equipo]['hechos'] = jugador.goles);

			// Calcular el Alcance Individual
			calcularAI = (jugador.goles * 100) /minimoGoles;
			alcanceIndividual = ( calcularAI > 100) ? 100 : calcularAI;
			jugador.alcance_individual = alcanceIndividual;
		});

		// Recorremos y hacemos las operaciones para los calculos finales.
		$.map(salarios, function(jugador, index){
			let calcularAG = 0, porcentajeBono = 0, bonoAlcanzado = 0;
			// Calculamos el porcentaje Grupal alcanzado
			calcularAG = ((equiposMetas[jugador.equipo]["hechos"] * 100) / equiposMetas[jugador.equipo]["minimo"]);
			calcularAG = (calcularAG > 100) ? 100 : calcularAG;

			// Sacamos la media porcentual entre el alcance Grupal y el individual
			porcentajeBono = (jugador.alcance_individual + calcularAG) / 2;

			// Calculamos el bono alcanzado respecto a la media obtenida
			bonoAlcanzado = (porcentajeBono * jugador.bono) / 100;

			// Agregamos al array el sueldo completo
			jugador.sueldo_completo = jugador.sueldo + bonoAlcanzado;

			// Eliminamos inforación que no ocupemos
			delete salarios[index]['alcance_individual'];
			delete salarios[index]['nivel'];
		});

		//Helper para el formato de tipo moneda para la tabla
		var formatter = new Intl.NumberFormat('en-US', {
			style: 'currency',
			currency: 'USD',
		});

		// Ordenamos el array por Equipos
		function compare( a, b ) {
			if ( a.equipo < b.equipo ){
				return -1;
			}
			if ( a.equipo > b.equipo ){
				return 1;
			}
			return 0;
		}

		salarios.sort(compare);


		// Validamos la salida del JSON 
		if(validaSalida(JSON.stringify(salarios))){

			// Tabla con la información del resultado
			let tabla = ``;
			$.each(salarios, function(index, jugadores){
				tabla += `
				<tr>
				<td>${jugadores.equipo}</td>
				<td>${jugadores.nombre}</td>
				<td>${jugadores.goles_minimos}</td>
				<td>${jugadores.goles}</td>
				<td>${formatter.format(jugadores.sueldo)}</td>
				<td>${formatter.format(jugadores.bono)}</td>
				<td>${formatter.format(jugadores.sueldo_completo)}</td>
				</tr>`;
			});

			$('#tabla tbody').html(tabla);
			$('#tabla').trigger('update', [0]);

			// Estilizamos la salida del JSON
			let resultado = JSON.stringify(salarios, undefined, 4);

			// Adjuntamos info al Textarea
			$('#resultado').html(resultado);

			// Creamos la liga de descarga y agregamos al DIV donde se agregara el boton
			let descargar = "text/json;charset=utf-8," + encodeURIComponent(resultado);
			$('.descargar-archivo').html('<a href="data:' + descargar + '" download="Sueldos-Completos.json" class="btn-gral">DESCARGAR JSON</a>');

			// Mostramos los resultados
			$("#main-box").slideUp(100);
			$(".caja-resultados").slideDown(100);

			$('#ajaxBusy').fadeOut(300);


		}else if(salarios == undefined){
			$('#ajaxBusy').fadeOut(300);
			// Caso contrario que no nos devuelva la información correcta mostramos mensaje
			Swal.fire({
				type: 'error',
				title: 'Error al procesar',
				text: 'Verifique que sus archivos tengan el formato solicitado para procesar su información correctamente.',
			})
		}
	});

$('.refrescar').click(function(){
	$('#ajaxBusy').fadeIn(300);
});
});
// Validamos que no provoque errores el JSON
function validaSalida(str) {
	try {
		JSON.parse(str);
	} catch (e) {
		return false;
	}
	return true;
}