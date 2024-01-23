
const btnNovoClick = () => {
	
	const formulario = document.querySelector('#form');
		
	formulario.reset();
	
	document.querySelector("input[type='file']").value = '';
	
	document.getElementById('id').value = '';
	
	formulario.action = 'animes/insert';

	const tituloElement = document.querySelector('#titulo');
	if (tituloElement) {
		tituloElement.textContent = 'Novo';
	}
	
	//document.querySelector('#capa').src = 'dist/images/animes/capa.png';

	$('#collapseFalecimento').collapse('hide');

	$('#modal').modal();
}

const btnEditarClick = (event) => {
	let nomeInput           = document.querySelector('input[name="nome"]');
	let dataLancamentoInput = document.querySelector('input[name="dataLancamento"]');
	let temporadasInput     = document.querySelector('input[name="temporadas"]');

	const tr             = btn.closest('tr');
	const dataInfo       = tr.dataset.info;
	const info           = JSON.parse(dataInfo);
	const id             = info.serie.id;		
	const nome           = info.serie.nome;
	const generos        = info.serie.genero_id;
	const dataLancamento = info.serie.data_lancamento;
	const temporadas     = info.serie.temporadas;

	console.log(info.serie);

	let idsArray = generos ? generos.split(',').map(function(id) {
		return id.trim();
	}) : [];

	$('#select2').val(null).trigger('change');

	idsArray.forEach(function(id) {
		$('#select2').find('option[value="' + id + '"]').prop('selected', true);
	});
	$('#select2').trigger('change');

	const idInput = document.createElement('input');
	idInput.type = 'hidden';
	idInput.name = 'id';
	idInput.value = id;
	form.appendChild(idInput);
	form.action = 'series/update';

	nomeInput.value           = nome;
	dataLancamentoInput.value = dataLancamento;
	temporadasInput.value     = temporadas;
}

const btnDeletarClick = (event) => {
	const btn      = event.currentTarget;
	tr             = btn.closest('tr');
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
			fetch('/animes/delete/' + id, {
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
}

const Submit = (event) => {
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
				location.reload(true);
			}, 1100);
	}).catch(error => {
		console.error('Erro na requisição:', error);
	});	
}

const Start = () => {

	$.ajax({
		url: 'generos/listar',
		dataType: 'json',
		success: function(data) {
			$('.select2').select2({
				placeholder: 'Selecione as opções',				
				data: data.results
			});				
		}
	});

	fetch('dist/js/pt-BR.json')
	.then(response => response.json())
	.then(ptBR => {		
		$('#tbAnimes').DataTable({
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

	const btnNovo = document.querySelector('.novo');
	const buttons = document.querySelectorAll('.editar, .deletar');

	btnNovo.addEventListener('click', handleClick);

	buttons.forEach((btn) => {
		btn.addEventListener('click', handleClick);
	});

	let cells = document.querySelectorAll('.imagem-cell');
	cells.forEach(function(cell) {
			cell.addEventListener('click', function() {
					let caminhoImagem = 'dist/images/' + this.getAttribute('data-imagem');
					document.getElementById('modalImagem').src = caminhoImagem;
					$('#imagemModal').modal('show');
			});
	});

	const btnSubmit = document.querySelector('#form');
	btnSubmit.addEventListener('submit', Submit);
});