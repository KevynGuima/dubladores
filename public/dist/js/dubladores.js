//jeito moderno de escrever uma função, antigamente tinha que escrever assim: "function btnNovoClick() {"
const btnNovoClick = () => {
	
	// pega o formulário onde está id="form" <form id="form" method="post" (id é # e class é .)
	const formulario = document.querySelector('#form');//pode usar tbm document.getElementById('form')
	
	// resetando o formulario ou seja limpando todos os campos, mas lembrando que esse reset nao funciona em todos os
	// tipos de campos	
	formulario.reset();
	
	// por exemplo campo file o reset não funciona
	document.querySelector("input[type='file']").value = '';
	
	// campos ocultos(invisiveis) tambem o reset não funciona <input type="hidden" name="id" id="id"
	document.getElementById('id').value = '';
	
	// se clica no botão novo, o formulário vai ser enviado para o insert
	formulario.action = 'dubladores/insert';

	// pego o título da modal para escrever 'Novo'	
	const tituloElement = document.querySelector('#titulo');
	if (tituloElement) {
		tituloElement.textContent = 'Novo';
	}
	
	// coloca uma imagem padrão se não foi feito nenhum upload
	document.querySelector('#retrato').src = 'dist/images/dubladores/retrato.png';

	// coloca o campo data_falecimento no "oculto"
	$('#collapseFalecimento').collapse('hide');

	// Preste atenção! É importante setar tudo para evitar bugs, pense se você vai clicar no botao novo,
	// editar, e depois em novo. Se não setar os campos pode bagunçar tudo
	
	// mando abrir a modal
	$('#modal').modal();
}

const btnEditarClick = (event) => {
	// event ele pega muita coisa mas no caso estamos pegando quem clicou no botão
	btn  = event.currentTarget;
	
	// pega o formulário onde está id="form", pois toda função precisa criar variáveis novamente 
	const formulario = document.querySelector('#form');
	
	// localStorage é um jeito de salvar os dados no navegador do usuário e pegar usando javascript por setItem(),
	// getItem(), removeItem() e clear()
	localStorage.removeItem('data_falecimento');
	// resetando
	formulario.reset();
	document.querySelector("input[type='file']").value = '';
	document.querySelector('input[name="data_falecimento"]').value = '';
	formulario.action = 'dubladores/update';

	// Resumidamente quando você ver $('algo') é jquery, jquery pode ser muito grande, porém 
	// diminui muitas linhas de código usá-lo
	$('#modal').off('hidden.bs.modal').on('hidden.bs.modal', function (e) {
		$('#collapseFalecimento').collapse('hide');
	});	

	document.querySelector('#retrato').src = 'dist/images/dubladores/retrato.png';
	document.querySelector('#retrato').style.filter = '';
	
	let tituloElement = document.querySelector('#titulo');
	if (tituloElement) {
		tituloElement.textContent = 'Editar';
	}
	
	// O método closest() é um método JavaScript utilizado para encontrar o antecessor mais próximo de um elemento que
	// corresponda a um determinado seletor.
	// Ele é usado para encontrar a linha (<tr>) da tabela que contém o botão (btn) que foi clicado:
	// por isso que ele retorna é um tr <tr>
	tr = btn.closest('tr');
	
	let tds = tr.children;
	console.log(tds[0].textContent.trim());
	console.log(tds[1].textContent.trim());
	console.log(tds[2].textContent.trim());
	
	// <tr data-info='{{ {"dublador": dublador} | json_encode }}'>
	// todo o objeto enviado nesse data-info passa para decodificação json
	let dataInfo         = tr.dataset.info;
	// e assim que ele é enviado :
	// JSON.parse(dataInfo) é o json_decode 
	//<tr data-info="{&quot;dublador&quot;:{
	// &quot;id&quot;:11,&quot;nome&quot;:&quot;Cassius Romero&quot;,&quot;data_nascimento&quot;:&quot;2023-01-01&quot;,
	// &quot;data_falecimento&quot;:&quot;2020-10-05&quot;,&quot;imagem&quot;:&quot;Cassius Romero.jpg&quot;,
	// &quot;sexo&quot;:&quot;M&quot;
  //}}" role="row" class="odd">
	// porém ele faz parse(dataInfo) para a variavel info, e permite usar como um array
	let info             = JSON.parse(dataInfo);

	let id               = info.dublador.id;
	let nome             = info.dublador.nome;
	let sexo             = info.dublador.sexo;
	let imagem           = info.dublador.imagem;
	let data_nascimento  = info.dublador.data_nascimento;
	// aqui tem uma tratativa de erro para caso esteja null ou 'null', não é colocado valor em data_falecimento.
	// caso contrário, coloque a data que foi recebida
	let data_falecimento = (
		info.dublador.data_falecimento === null || info.dublador.data_falecimento === 'null'
	) ? '' : info.dublador.data_falecimento;

	$('#modal').off('shown.bs.modal').on('shown.bs.modal', function (e) {
		if (data_falecimento !== null && data_falecimento !== 'null' && data_falecimento.length != 0) {
			document.querySelector('input[name="data_falecimento"]').value = data_falecimento;
			$('#collapseFalecimento').collapse('show');
		} else {
			document.querySelector('input[name="data_falecimento"]').value = '';
		}
	});

	// aqui está salvando os dados que pegamos do banco que estavam na tabela na linha <tr> para comparar depois no
	// submit se teve alguma alteração no formulário
	localStorage.setItem('nome', nome);
	localStorage.setItem('sexo', sexo);
	localStorage.setItem('data_nascimento', data_nascimento);
	localStorage.setItem('data_falecimento', data_falecimento);
	
	// preenchendo os dados do formulario
	document.querySelector('input[name="id"]').value = id;
	document.querySelector('input[name="nome"]').value = nome;
	// aqui é colocado exatamente o que foi recebido na variavel sexo (sendo 'M' para masculino ou 'F' para feminino) 
	// para [value="'+sexo+'"] no input e habilita o .checked = true para que marque essa opção no sexo
	document.querySelector('input[name="sexo"][value="' + sexo + '"]').checked = true;
	document.querySelector('input[name="data_nascimento"]').value = data_nascimento;
	
	// verifica se tem imagem
	if(imagem) {
		document.querySelector('#retrato').src = 'dist/images/dubladores/' + imagem;
		// se tiver data_falecimento, coloca a imagem em preto e branco
		if(data_falecimento) {	
			document.querySelector('#retrato').style.filter = 'grayscale(100%)';
		}
	}
	
	//abre a modal ja com o formulário preenchido para edição
	$('#modal').modal();	
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
}

function Submit(event) {
	event.preventDefault();
	
	let nomeOrig             = localStorage.getItem('nome');
	let sexoOrig             = localStorage.getItem('sexo');
	let data_nascimentoOrig  = localStorage.getItem('data_nascimento');
	let data_falecimentoOrig = localStorage.getItem('data_falecimento');

	data_nascimentoOrig = (data_nascimentoOrig === null || data_nascimentoOrig === 'null') ? '' : data_nascimentoOrig;
	data_falecimentoOrig = (data_falecimentoOrig === null || data_falecimentoOrig === 'null') ? '' : data_falecimentoOrig;
	
	let formData         = new FormData(this);
	let id               = formData.get('id');
	let nome             = formData.get('nome');
	let sexo             = formData.get('sexo');
	let data_nascimento  = formData.get('data_nascimento');
	let data_falecimento = formData.get('data_falecimento');
	let imagem           = formData.get('imagem');

	if(id != '') {	
		if(
				nomeOrig === nome && sexoOrig === sexo && data_nascimentoOrig === data_nascimento && 
				data_falecimentoOrig === data_falecimento && imagem.size === 0
			) {
			Swal.fire({
			  position: 'top-end',
			  icon: 'info',
			  title: 'Nada foi alterado',
			  showConfirmButton: false,
			  timer: 1000
			});
			return false;
		}
	}

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
				//let tds            = tr.children;
				//tds[0].textContent = nome;
				//tds[1].textContent = sexo;
				//tds[2].textContent = data_nascimento;
				//$('#modal').modal('hide');				
			}, 1100);
	}).catch(error => {
		console.error('Erro na requisição:', error);
	});	
}

const Start = () => {
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

//o inicio de tudo
document.addEventListener('DOMContentLoaded', () => {
	let tr;
	
	Start();
	$('[data-toggle="tooltip"]').tooltip();
	
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
			document.querySelector('#nome').focus();
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
