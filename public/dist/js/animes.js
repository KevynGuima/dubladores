
function handleClick(event) {
	const btn  = event.currentTarget;
	const form = document.querySelector('#form');
		
	setTimeout(function() {
		form.querySelector('#nome').focus();
	}, 500);
		
	if (btn.classList.contains('novo')) {
		form.reset();
		form.action = 'animes/insert';
		$('#select2').val(null).trigger('change');
	} else if(btn.classList.contains('editar')) {
		let nomeInput           = document.querySelector('input[name="nome"]');
		let dataLancamentoInput = document.querySelector('input[name="data_lancamento"]');
		let temporadaInput      = document.querySelector('input[name="temporadas"]');
		let idInput             = document.querySelector('input[name="id"]');

		const tr              = btn.closest('tr');
		const dataInfo        = tr.dataset.info;
		const info            = JSON.parse(dataInfo);
		const id              = info.anime.id;
		const nome            = info.anime.nome;
		const generos         = info.anime.genero_id;
		const temporadas      = info.anime.temporadas;
		const imagem          = info.anime.imagem;
		const data_lancamento = info.anime.data_lancamento;

		let idsArray = generos ? generos.split(',').map((id) => {
			return id.trim();
		}) : [];

		$('#select2').val(null).trigger('change');

		idsArray.forEach((id) => {
			$('#select2').find('option[value="' + id + '"]').prop('selected', true);
		});
		$('#select2').trigger('change');
		
		form.action = 'animes/update';

		idInput.value             = id;
		nomeInput.value           = nome;
		dataLancamentoInput.value = data_lancamento;
		temporadaInput.value      = temporadas;
	} else if (btn.classList.contains('deletar')) {
		const tr       = btn.closest('tr');
		const dataInfo = tr.dataset.info;
		const info     = JSON.parse(dataInfo);
		const id       = info.anime.id;

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

	if(btn.classList.contains('novo') || btn.classList.contains('editar')) {
		$('#modal').modal();
	}
}

function Submit(event) {
	event.preventDefault();
	
	let formData = new FormData(this);

	let requestOptions = {
		method: 'POST',
		body: formData
	};

	axios.post(this.action, formData)
	.then(response => {
		if (response.status === 200) {
			Swal.fire({
				position: 'top-end',
				icon: 'success',
				title: 'Editado com sucesso!',
				showConfirmButton: false,
				timer: 1200,
				timerProgressBar: true,
				willClose: () => {
					location.reload(true);
				}
			});
		}
		if (response.status === 201) {
			Swal.fire({
				position: 'top-end',
				icon: 'success',
				title: 'Criado com sucesso!',
				showConfirmButton: false,
				timer: 1200,
				timerProgressBar: true,
				willClose: () => {
					location.reload(true);
				}
			});
		} else {
			console.log('Código de status inesperado:', response.status);
			console.log(response);
		}
	})
	.catch(error => {
	    console.error('Erro na solicitação:', error.response.status);
	    console.log(error);
		
		Swal.fire({
			position: 'top-end',
			icon: 'error',
			title: 'Erro!',
			showConfirmButton: false,
			timer: 1200,
			timerProgressBar: true,
			willClose: () => {
				location.reload(true);
			}
		});		
	});
  
	
	//setTimeout(function() {
		//location.reload(true);
	//}, 1100);	
}

const Start = () => {
	//$.fn.select2.defaults.set('theme', 'classic');

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
// Inicio do Script
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