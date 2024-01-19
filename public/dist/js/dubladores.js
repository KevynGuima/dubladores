
const btnNovoClick = () => {
	const formulario = document.querySelector('#form');	
	formulario.reset();
	formulario.action = 'dubladores/insert';
	const tituloElement = document.querySelector('#titulo');
	document.querySelector('#retrato').src = 'dist/images/dubladores/retrato.png';

	if (tituloElement) {
		tituloElement.textContent = 'Novo';
	} 

	$('#collapseFalecimento').collapse('hide');

	$('#modal').modal();
}

const btnEditarClick = (event) => {
	const btn  = event.currentTarget;
	const formulario = document.querySelector('#form');	
	formulario.action = 'dubladores/update';
	const tituloElement = document.querySelector('#titulo');
	$('#collapseFalecimento').collapse('hide');
	document.querySelector('#retrato').src = 'dist/images/dubladores/retrato.png';
	document.querySelector('#retrato').style.filter = '';

	if (tituloElement) {
		tituloElement.textContent = 'Editar';
	} 
	
	const tr               = btn.closest('tr');
	const dataInfo         = tr.dataset.info;
	const info             = JSON.parse(dataInfo);		
	const id               = info.dublador.id;
	const nome             = info.dublador.nome;
	const sexo             = info.dublador.sexo;
	const imagem           = info.dublador.imagem;
	const data_nascimento  = info.dublador.data_nascimento;
	const data_falecimento = info.dublador.data_falecimento;		
	
	document.querySelector('input[name="id"]').value = id;
	document.querySelector('input[name="nome"]').value = nome;
	document.querySelector('input[name="sexo"][value="' + sexo + '"]').checked = true;
	document.querySelector('input[name="data_nascimento"]').value = data_nascimento;
		
	if(data_falecimento) {
		document.querySelector('input[name="data_falecimento"]').value = data_falecimento;
		$('#collapseFalecimento').collapse('show');
	}
	
	if(imagem) {
		document.querySelector('#retrato').src = 'dist/images/dubladores/' + imagem;
	
		if(data_falecimento) {	
			document.querySelector('#retrato').style.filter = 'grayscale(100%)';
		}
	}
	
	$('#modal').modal();	
}

function btnDeletarClick(event) {
	const btn  = event.currentTarget;
	
	$('#select2').val(null).trigger('change');


		
		//var dubladorData = JSON.parse(row.getAttribute('data-info'));

		// Agora você pode acessar as propriedades do dublador
		console.log(info);		
		const id               = info.dublador.id;
		const nome             = info.dublador.nome;
		const sexo             = info.dublador.sexo;
		const data_nascimento  = info.dublador.data_nascimento;
		const data_falecimento = info.dublador.data_falecimento;

		//let idsArray = generos ? generos.split(',').map(function(id) {
			//return id.trim();
		//}) : [];		
		//$('#select2').val(null).trigger('change');
		//idsArray.forEach(function(id) {
			//$('#select2').find('option[value="' + id + '"]').prop('selected', true);
		//});
		//$('#select2').trigger('change');
		


		nomeInput.value = nome;
		sexoMInput.checked = false;
		sexoFInput.checked = false;
		if(sexo == "M") {
			sexoMInput.checked = true;
		} else {
			sexoFInput.checked = true;
		}
		nascimentoInput.value = data_nascimento;
		falecimentoInput.value = data_falecimento;

$('#modal').modal();

/*
		const tr       = btn.closest('tr');
		const dataInfo = tr.dataset.info;
		const info     = JSON.parse(dataInfo);
		const id       = info.id;	
		
		Swal.fire({
		  title: 'Você tem certeza?',
		  text: 'Você não poderá reverter essa ação!',
		  icon: 'question',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  cancelButtonText: 'Cancelar',
		  confirmButtonText: 'Sim, deletar!'
		}).then((result) => {
			if (result.isConfirmed) {		  
				fetch('/dubladores/delete/' + id, {
					method: 'DELETE',
					headers: {
						'Content-Type': 'application/json'
					}
				}).then(response => {
					if (response.status === 204) {
						Swal.fire({
						  position: 'top-end',
						  icon: 'success',
						  title: 'Deletado com sucesso!',
						  showConfirmButton: false,
						  timer: 1000
						});

						setTimeout(function() {
							location.reload(true);
						}, 1100);					
					} else {
					  console.error('Erro ao apagar o registro. Código de resposta:', response.status);
					  response.text().then(text => console.error('Conteúdo da resposta:', text));
					}
				})
				.catch(error => {
					console.error('Erro na requisição:', error);
				});
			}
		});
	
	*/
	//if(btn.classList.contains('novo') || btn.classList.contains('editar')) {
		//$('#modal').modal();
	//}
}

function Submit(event) {
	event.preventDefault();
	
	let formData = new FormData(this);

	let requestOptions = {
		method: 'POST',
		body: formData
	};

	fetch(this.action, requestOptions)
		.then(response => {
			if (response.status === 201) {
				Swal.fire({
				  position: 'top-end',
				  icon: 'success',
				  title: 'Criado com sucesso',
				  showConfirmButton: false,
				  timer: 1000
				});
			}
			
			if (response.status === 200) {
				Swal.fire({
				  position: 'top-end',
				  icon: 'success',
				  title: 'Editado com sucesso',
				  showConfirmButton: false,
				  timer: 1000
				});
			}			

			setTimeout(function() {
				//location.reload(true);
			}, 1100);
	}).catch(error => {
		console.error('Erro na requisição:', error);
	});	
}

const Start = () => {
	//$.fn.select2.defaults.set('theme', 'classic');

	/*$.ajax({
		url: 'generos/listar',
		dataType: 'json',
		success: function(data) {
			$('.select2').select2({
				placeholder: 'Selecione as opções',				
				data: data.results
			});				
		}
	});*/

	fetch('dist/js/pt-BR.json')
	.then(response => response.json())
	.then(ptBR => {		
		$('#tbDubladores').DataTable({
			'language': ptBR,
			'processing': true,
			'serverSide': false,
			'columnDefs': [
				{ 'orderable': false, 'targets': [2, 3] }
			],
			'order': [
				[0, 'asc']
			]
		});
	})
	.catch(error => {
		console.error('Erro ao carregar o JSON:', error);
	});
}

document.addEventListener('DOMContentLoaded', () => {
	Start();
	$('[data-toggle="tooltip"]').tooltip()
	
	const btnNovo    = document.querySelector('.novo');
	const btnEditar  = document.querySelectorAll('.editar');
	const btnDeletar = document.querySelectorAll('.deletar');	
	const formulario = document.querySelector('#form');
	
	btnNovo.addEventListener('click', btnNovoClick);

	btnEditar.forEach((btn) => {
		btn.addEventListener('click', btnEditarClick);
	});

	btnDeletar.forEach((btn) => {
		btn.addEventListener('click', btnDeletarClick);
	});

	formulario.addEventListener('submit', Submit);

	$('#modal').on('shown.bs.modal', () => {
		setTimeout(() => {
			formulario.querySelector('input').focus();
		}, 500);
	});

    let cells = document.querySelectorAll('.imagem-cell');
    cells.forEach(function(cell) {
        cell.addEventListener('click', function() {
			document.querySelector('#modalImagem').style.filter = '';
			
			let falecimentoImg = this.getAttribute('data-falecimento');
			
			let caminhoImagem = 'dist/images/dubladores/';
			
			if(this.getAttribute('data-imagem')) {
				caminhoImagem = caminhoImagem + this.getAttribute('data-imagem');
			} else {
				caminhoImagem = caminhoImagem + 'retrato.png';
			}

			document.querySelector('#modalImagem').src = caminhoImagem;
			if(falecimentoImg) {
				document.querySelector('#modalImagem').style.filter = 'grayscale(100%)';
			}

			const modalRetrato = document.querySelector('#modalRetrato');

			if (modalRetrato) {
				modalRetrato.innerHTML = this.getAttribute('data-nome');
			}
	
            $('#imagemModal').modal('show');
        });
    });
});
