const animateCSS = (element, animation, prefix = 'animate__') =>
	new Promise((resolve, reject) => {
		const animationName = `${prefix}${animation}`;

		element.classList.add(`${prefix}animated`, animationName);

		function handleAnimationEnd(event) {
			event.stopPropagation();
			element.classList.remove(`${prefix}animated`, animationName);
			resolve('Animation ended');
		}

		element.addEventListener('animationend', handleAnimationEnd, {once: true});
});

function OpenModalSave(event) {
	let btn = event.currentTarget;
	
	document.querySelector('.modal-title').textContent = 'Editar';	
	
	setTimeout(function() {
		form.querySelector('input').focus();
	}, 500);

	const tr       = btn.closest('tr');	
	cells          = tr.querySelectorAll('td');		
	const id       = tr.dataset.id;
	const dataInfo = tr.dataset.info;
	const info     = JSON.parse(dataInfo);	
	const p        = window.location.href.split('/');

	form.action    = p[p.length - 1] + '/update';
	
	const inputID     = document.querySelector('input[name="id"]');
	const inputGenero = document.querySelector('input[name="genero"]').value = cells[0].textContent;

	if (inputID === null) {
		const idInput = document.createElement('input');
		idInput.type  = 'hidden';
		idInput.name  = 'id';
		idInput.value = id;
		form.appendChild(idInput);
	} else {
		inputID.value = id;
	}

	$('#modal').modal();
}

function Deletar(event) {
	let btn  = event.currentTarget;
	const tr = btn.closest('tr');
	const id = tr.dataset.id;
	const p  = window.location.href.split('/');	
	let url  = p[p.length - 1] + '/delete/' + id;

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
				if (!response.ok) {
					return response.json().then(errorData => {
						Swal.fire({
							icon: 'error',
							title: errorData.message,
							showConfirmButton: false,
							timerProgressBar: true,
							timer: 2000
						});			
						throw new Error(errorData.message);
					});
				} else {
				  if(response.status === 204) {
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
				}

				return response.json();
			  })
			  .then(data => {
					console.log(data);		
			  })
			  .catch(error => {
				console.error('Error na requisição:', error.message);
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
					//cells[0].textContent = document.querySelector('input[name="genero"]').value;
					//animateCSS(cells[0], 'flash');
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
	let cells; 
	
	const form = document.querySelector('#form');
	form.addEventListener('submit', Submit);

	fetch('dist/js/pt-BR.json')
	.then(response => response.json())
	.then(ptBR => {
		$('table').DataTable({
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
	
	document.querySelector('#btnNovo').addEventListener('click', () => {
		document.querySelector('.modal-title').textContent = 'Novo';
		
		const p    = window.location.href.split('/');
		const url  = p[p.length - 1];
	
		setTimeout(function() {
			form.querySelector('input').focus();
		}, 500);

		form.reset();
		form.action = url + '/insert';
		$('#modal').modal();
	});
	
	document.querySelectorAll('.editar').forEach((btn) => {
		btn.addEventListener('click', OpenModalSave);
	});

	document.querySelectorAll('.deletar').forEach((btn) => {
		btn.addEventListener('click', Deletar);
	});	
});
