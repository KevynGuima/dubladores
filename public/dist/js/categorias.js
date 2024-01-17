
function OpenModalSave(event) {
	const isNew = event.currentTarget.getAttribute('data-action') === 'novo';

	const form = document.querySelector('#form');
	let btn    = event.currentTarget;
	
	setTimeout(function() {
		form.querySelector('input').focus();
	}, 500);
		
	let categoriaInput = document.querySelector('input[name="categoria"]');

	if(isNew) {		
		document.querySelector('.modal-title').textContent = 'Nova Categoria';
		 
		form.reset();
		form.action = 'categorias/insert';		
		
	} else {
		document.querySelector('.modal-title').textContent = 'Editando Categoria';

		//let id        = btn.dataset.id;
		let row       = btn.closest('tr');		
	    const id      = row.dataset.id;
		let categoria = row.cells[0].innerText;
		
		const idInput = document.createElement('input');
		idInput.type = 'hidden';
		idInput.name = 'id';
		idInput.value = id;
		form.appendChild(idInput);
		form.action = 'categorias/update';

		categoriaInput.value = categoria;
	}	
	
	$('#modal').modal();
}

function Delete(event) {
	let btn  = event.currentTarget;
	const tr = btn.closest('tr');
	const id = tr.dataset.id;
	
	let url = '/categorias/delete/' + id;

	// Configuração da requisição
	let requestOptions = {
	  method: 'DELETE',
	  headers: {
		'Content-Type': 'application/json'		
	  }
	};

	Swal.fire({
	  title: "Você tem certeza?",
	  text: "Você não poderá reverter essa ação!",
	  icon: "warning",
	  showCancelButton: true,
	  confirmButtonColor: "#3085d6",
	  cancelButtonColor: "#d33",
	  cancelButtonText: "Cancelar",
	  confirmButtonText: "Sim, deletar!"
	}).then((result) => {
		if (result.isConfirmed) {		  
			fetch(url, requestOptions)
			  .then(response => {
				if (response.status === 204) {
					Swal.fire({
						position: 'top-end',
						icon: 'success',
						title: 'Deletado com sucesso!',
						showConfirmButton: false,
						timer: 1200,
						timerProgressBar: true,
						willClose: () => {
							location.reload(true);
						}
					});
				}
			})
			.catch(error => {
				Swal.fire({
					icon: 'error',
					title: error,
					showConfirmButton: false,
					timerProgressBar: true,
					timer: 2000
				});
			});
		}
	});
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
		if (!response.ok) {
			//throw new Error('Erro na requisição');
		}
		
		return response.json();
	}).then(data => {
		if(data.success) {
			$('#modal').modal('hide');

			Swal.fire({
				position: 'top-end',
				icon: 'success',
				title: data.message,
				showConfirmButton: false,
				timer: 1200,
				timerProgressBar: true,
				willClose: () => {
					location.reload(true);
				}
			});
		} else {
		  	Swal.fire({
			  icon: 'error',
			  title: data.message,
			  showConfirmButton: false,
			  timer: 2000,
			  timerProgressBar: true
			});
		}		
	}).catch(error => {
		Swal.fire({
			icon: 'error',
			title: error,
			showConfirmButton: false,
			timerProgressBar: true,
			timer: 2000
		});
		//console.log('Erro:', error);
		console.error('Erro:', error);
	});
}

document.addEventListener('DOMContentLoaded', () => {
	fetch('dist/js/pt-BR.json')
	.then(response => response.json())
	.then(ptBR => {		
		$('#tbCategorias').DataTable({
			'language': ptBR,
			'processing': true,
			'serverSide': false,
			'columnDefs': [
				{ 'orderable': false, 'targets': [1] }
			],
			'order': [
				[0, 'asc']
			]
		});
	})
	.catch(error => {
		console.error('Erro ao carregar o JSON:', error);
	});
	
	const btnNew    = document.querySelector('#btnNovo');
	const btnEdit   = document.querySelectorAll('.edit');
	const btnDel    = document.querySelectorAll('.delete');
	const btnSubmit = document.querySelector('#form');
	
	btnNew.setAttribute('data-action', 'novo');
	btnNew.addEventListener('click', OpenModalSave);
	
	btnEdit.forEach((btn) => {
		btn.addEventListener('click', OpenModalSave);
	});

	btnDel.forEach((btn) => {
		btn.addEventListener('click', Delete);
	});
	
	btnSubmit.addEventListener('submit', Submit);

/*

	const btnModal = document.querySelector('#modal');

	btnModal.addEventListener('click', (event) => {
		console.log('abri modal', event.type);
		
		//metodo 1
		document.querySelector('input[name="nome"]').value = '';
		document.querySelector('input[name="email"]').value = '';
		document.querySelector('input[name="senha"]').value = '';
		document.querySelector('input[name="dataNascimento"]').value = '';
		
		//metodo 2
		const inputs = document.querySelectorAll('input');
		inputs.forEach(input => {
			input.value = '';
		});
		
		//metodo 3
		const formulario = document.getElementById('formUser');
		formulario.reset();

		$("#userModal").modal();
		//const bootstrapModal = new bootstrap.Modal(modal);
		//bootstrapModal.show();
		
		//form.reset();

		//setTimeout(function() {
			//form.querySelector('input').focus();
		//}, 500);
		
		//descobrir quem disparou o evento relatedTarget
		//const button    = event.relatedTarget;
		//const recipient = button.getAttribute('data-bs-whatever')
		//console.log(button);
		//console.log(recipient);
	});
	

	
	//$('#tbUsuarios').on('click', '.edit', function() {
		//var userId = $(this).data('id');
		//console.log(userId);
	//});		
	//$('#tbUsuarios').on('click', '.delete', function() {
		//var userId = $(this).data('id');
		//console.log(userId);
	//});		

	let btnEditar  = document.querySelectorAll('.edit');
	let btnDeletar = document.querySelectorAll('.delete');
	
	// Adicione um manipulador de eventos de clique para cada botão "edit"
	btnEditar.forEach((btn) => {
		btn.addEventListener('click', () => {
			let dataId = btn.dataset.id;
			let dataNascimento = btn.closest('tr').querySelector('[data-nascimento]').getAttribute('data-nascimento');				

			// Acesse a linha (tr) pai do botão
			let row = btn.closest('tr');

			// Obtenha os textos das células correspondentes
			let nome = row.cells[1].innerText;
			let email = row.cells[2].innerText;

			document.querySelector('input[name="nome"]').value = nome;
			document.querySelector('input[name="email"]').value = email;
			document.querySelector('input[name="dataNascimento"]').value = dataNascimento;
			
			$("#userModal").modal();
			
			$.ajax({
				url: '/usuarios/listar', // Substitua pela URL correta do seu servidor
				method: 'GET',
				dataType: 'json', // Espera uma resposta JSON do servidor
				success: function(response) {
					// Manipule os dados recebidos (response) aqui
					console.log(response);
					tabelaUsuarios.ajax.reload();
				},
				error: function(error) {
					// Manipule erros aqui, se houver algum
					console.error('Erro na requisição:', error);
				}
			});			
		});
	});
	
	
	btnDeletar.forEach((btn) => {
		btn.addEventListener('click', () => {
			let dataId = btn.dataset.id;

			// Acesse a linha (tr) pai do botão
			let row = btn.closest('tr');

			// Obtenha os textos das células correspondentes
			let nome = row.cells[1].innerText;
			let email = row.cells[2].innerText;

			// Faça o que quiser com o id, nome e email
			console.log('ID:', dataId);
			console.log('Nome:', nome);
			console.log('Email:', email);				
			//tabelaUsuarios.ajax.reload();				
		});
	});
	*/
	
	$('#formCategoria').submit(function(event) {
		event.preventDefault();

		let formData = $(this).serialize();

		$.ajax({
			type: 'POST',
			url: 'categorias/insert',
			data: formData,
			success: function(response, status, xhr) {
				if (xhr.status === 201) {
					location.reload(true);
				}
			},
			error: function(error) {
			   let responseData = JSON.parse(error.responseText);

				// Verifica se a solicitação não foi bem-sucedida (success = false)
				if (!responseData.success) {
					console.log(responseData.message);

					// Exiba a mensagem de erro para o usuário ou tome outras ações necessárias
					alert('Erro: ' + responseData.message);
				}
			}
		});
	});		
});
