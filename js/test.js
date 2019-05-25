var salarios, equiposMetas, salarios_completos;

$.when(
	$.getJSON( "salarios.json", function( data ) {
		salarios = data;
	}),
	$.getJSON( "equipos.json", function( data ) {
		equiposMetas = data;
	}),
	$.getJSON( "salarios_completos.json", function( data ) {
		salarios_completos = data;
	})
).then(function(){
	// Declaramos variables Globales con las que se trabajaran
	// var salarios = [{"nombre":"Juan Perez", "nivel":"C", "goles":10, "sueldo":50000, "bono":25000, "sueldo_completo":null, "equipo":"rojo"}, {"nombre":"EL Cuauh", "nivel":"Cuauh", "goles":30, "sueldo":100000, "bono":30000, "sueldo_completo":null, "equipo":"azul"}, {"nombre":"Cosme Fulanito", "nivel":"A", "goles":7, "sueldo":20000, "bono":10000, "sueldo_completo":null, "equipo":"azul"}, {"nombre":"El Rulo", "nivel":"B", "goles":9, "sueldo":30000, "bono":15000, "sueldo_completo":null, "equipo":"rojo"} ] , equiposMetas = {"rojo" : {"A": 5, "B": 10, "C": 15, "Cuauh": 20 }, "azul" : {"A": 15, "B": 20, "C": 25, "Cuauh": 32 } }, salarios_completos = [{"nombre": "EL Cuauh", "goles": 30, "sueldo": 100000, "bono": 30000, "sueldo_completo": 125871.01063829787, "equipo": "azul", "goles_minimos": 32 }, {"nombre": "Cosme Fulanito", "goles": 7, "sueldo": 20000, "bono": 10000, "sueldo_completo": 26269.50354609929, "equipo": "azul", "goles_minimos": 15 }, {"nombre": "Juan Perez", "goles": 10, "sueldo": 50000, "bono": 25000, "sueldo_completo": 67833.33333333334, "equipo": "rojo", "goles_minimos": 15 }, {"nombre": "El Rulo", "goles": 9, "sueldo": 30000, "bono": 15000, "sueldo_completo": 42450, "equipo": "rojo", "goles_minimos": 10 } ]; 

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

	//Helper para el formato de tipo moneda para la tabla
	var formatter = new Intl.NumberFormat('en-US', {
		style: 'currency',
		currency: 'USD',
	});

	// Filtros de la tabla
	$('table').tablesorter();
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

			let registro = `<li>`;

			registro += `El jugador <strong>${jugador.nombre}</strong> del equipo <strong class="capital">${jugador.equipo}</strong> tiene el nivel <strong>${jugador.nivel}</strong>`;

			// Agrega la Meta Minimo de Goles Individual y las indexa en el array de Salarios
			minimoGoles = equiposMetas[jugador.equipo][jugador.nivel];
			jugador.goles_minimos = minimoGoles;

			if(equiposMetas[jugador.equipo]['minimo'] == undefined){
				equiposMetas[jugador.equipo]['minimo'] = 0;
			}

			if(equiposMetas[jugador.equipo]['hechos'] == undefined){
				equiposMetas[jugador.equipo]['hechos'] = 0;
			}
			
			registro += `<br/>- Su meta de goles es <strong>${minimoGoles}</strong>, esta meta se agrega a una sumatoria de <strong>meta de goles grupal</strong> que nos da como resultado: <strong>${minimoGoles} + ${equiposMetas[jugador.equipo]['minimo']} </strong> = `;

			// Agrega la Meta Minima de Goles que deben anotar por Equipo en el array de equiposMetas
			(equiposMetas[jugador.equipo]['minimo'] != undefined) ? (equiposMetas[jugador.equipo]['minimo'] +=  minimoGoles) : (equiposMetas[jugador.equipo]['minimo'] =  minimoGoles);
			registro += `<strong>${equiposMetas[jugador.equipo]['minimo']}</strong>.`;

			registro += `<br/>- Este jugador realizo <strong>${jugador.goles}</strong> goles los cuales se agregan al total <strong>goles realizados por equipo</strong> que nos da como resultado <strong>${jugador.goles} + ${equiposMetas[jugador.equipo]['hechos']}</strong> = `;

			// Global de Goles Hechos por equipo y las indexa en el array de equiposMetas
			(equiposMetas[jugador.equipo]['hechos'] != undefined) ? (equiposMetas[jugador.equipo]['hechos'] += jugador.goles) : (equiposMetas[jugador.equipo]['hechos'] = jugador.goles);
			
			registro += `<strong>${equiposMetas[jugador.equipo]['hechos']}</strong>.`;

			// Calcular el Alcance Individual
			calcularAI = (jugador.goles * 100) /minimoGoles;
			alcanceIndividual = ( calcularAI > 100) ? 100 : calcularAI;
			jugador.alcance_individual = alcanceIndividual;

			registro += `<br/>- Por lo tanto este jugador realizo <strong>${jugador.goles}</strong> de <strong>${minimoGoles}</strong> goles, obteniendo un <strong>Alcance individual</strong> del: <span class="tsecundario"> ${alcanceIndividual} %</span></strong>. `;
			registro  += `</li><div class="h10"></div>`;

			$('.metas-individuales').append(registro);
		});

		$.each(equiposMetas, function(index, equipo){
			let calcularAG = 0, porcentajeBono = 0, bonoAlcanzado = 0;
			// Calculamos el porcentaje Grupal alcanzado
			calcularAG = ((equipo.hechos * 100) / equipo.minimo);
			calcularAG = (calcularAG > 100) ? 100 : calcularAG;

			$('.metas-grupales').append(`<li>Equipo <strong class="capital tsecundario">${index}</strong> <strong>goles realizados</strong> <strong class="tsecundario">${equipo.hechos}</strong>, <strong>meta grupal</strong>  <strong class="tsecundario">${equipo.minimo}</strong> 
				obteniendo un <strong>Alcance Grupal</strong> del:  <span class="tsecundario"> ${calcularAG}%</span></li>`);
		});

		// Recorremos y hacemos las operaciones para los calculos finales.
		$.map(salarios, function(jugador, index){

			let calcularAG = 0, porcentajeBono = 0, bonoAlcanzado = 0, registro = `<li>`;
			// Calculamos el porcentaje Grupal alcanzado
			calcularAG = ((equiposMetas[jugador.equipo]["hechos"] * 100) / equiposMetas[jugador.equipo]["minimo"]);
			calcularAG = (calcularAG > 100) ? 100 : calcularAG;

			// Sacamos la media porcentual entre el alcance Grupal y el individual
			porcentajeBono = (jugador.alcance_individual + calcularAG) / 2;

			// Calculamos el bono alcanzado respecto a la media obtenida
			bonoAlcanzado = (porcentajeBono * jugador.bono) / 100;

			// Agregamos al array el sueldo completo
			jugador.sueldo_completo = jugador.sueldo + bonoAlcanzado;


			registro = `<li>Jugador <strong>${jugador.nombre}</strong> del equipo <strong class="capital">${jugador.equipo}</strong> obtuvo: 
			<br/><strong>Alcance individual:</strong> <span class="tsecundario">${jugador.alcance_individual}%</span>
			<br/><strong>Alcance grupal:</strong> <span class="tsecundario">${calcularAG}%</span>
			<br/><strong>Alcance total:</strong> <span class="tsecundario">${porcentajeBono}%</span> (Porcentaje que obtendrá de su bono)
			<br/>Este jugador tiene un <strong>Sueldo Base de</strong> <span class="tsecundario">${formatter.format(jugador.sueldo)}</span> y un <strong>Bono Máximo de</strong> <span class="tsecundario">${formatter.format(jugador.bono)}</span>
			<br/>Obtuvo el <span class="tsecundario">${porcentajeBono}%</span> de <span class="tsecundario">${formatter.format(jugador.bono)}</span> por lo tanto su <strong>Bono Alcanzado</strong> es de <span class="tsecundario">${formatter.format(bonoAlcanzado)}</span>
			<br/>Dando como resultado <strong>Sueldo Base <span class="tsecudnario">+</span> Bono alcanzado</strong> un total de <strong class="tsecundario">${formatter.format(jugador.sueldo_completo)}</strong>
			</li><div class="h10"></div>`;

			// Eliminamos inforación que no ocupemos
			delete salarios[index]['alcance_individual'];
			delete salarios[index]['nivel'];

			$('.metas-bonos').append(registro);
		});


		if(JSON.stringify(salarios_completos) === JSON.stringify(salarios)){
			$('.json-equal').html('correctos.');
		}else{
			$('.json-equal').html('incorrectos.');
		}

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


// Validamos que no provoque errores el JSON
function validaSalida(str) {
	try {
		JSON.parse(str);
	} catch (e) {
		return false;
	}
	return true;
}