//jeito moderno de escrever uma função, antigamente tinha que escrever assim: "function btnNovoClick() {"
const btnNovoClick = () => {
	
	//# representa id e . = class  id="nome" class="nome"
	//estou pegando o formulario onde esta id="form" <form id="form" method="post"
	const formulario = document.querySelector('#form');//pode usar tbm document.getElementById('form')
	
	//resetando o formulario ou seja limpando todos os campos, mas lembrando que esse reset nao funciona em todos os tipos de campos	
	formulario.reset();
	
	//por exemplo campo file o reset nao funciona
	document.querySelector("input[type='file']").value = '';
	
	//campos ocultos(invisiveis)  tbm o reset nao funciona <input type="hidden" name="id" id="id"
	document.getElementById('id').value = '';
	
	//se clica no botao novo o formulario vai ser enviado para o insert
	formulario.action = 'dubladores/insert';

	// pego o titulo da modal para escrever 'Novo'	
	const tituloElement = document.querySelector('#titulo');
	if (tituloElement) {
		tituloElement.textContent = 'Novo';
	}
	
	//seto uma imagem padrao se nao foi feito nenhum upload
	document.querySelector('#retrato').src = 'dist/images/dubladores/retrato.png';

	//seto o campo data_falecimento para ocultar ele
	$('#collapseFalecimento').collapse('hide');

	//presta atenção é importante setar tudo para evitar bugs, pensa assim vc vai clicar no botao novo, depois do editar, e depois em novo dai se nao setar os campos pode bagunçar tudo
	
	//mando abrir a modal
	$('#modal').modal();
}

const btnEditarClick = (event) => {
	//event ele pega muita coisa mas no caso estamos pegando quem clicou no botao
	btn  = event.currentTarget;
	
	//aqui vc ja sabe senao olha a função btnNovoClick
	const formulario = document.querySelector('#form');
	
	//localStorage é um jeito de salvar os dados no navegador do usuario e pegar usando javascript setItem(), getItem() , removeItem() e clear()
	localStorage.removeItem('data_falecimento');
	//resetando
	formulario.reset();
	document.querySelector("input[type='file']").value = '';
	document.querySelector('input[name="data_falecimento"]').value = '';
	formulario.action = 'dubladores/update';

	//resumidamente quando vc ver $('algo) é jquery, jquery é muito gigante e facilita muito ele resume muito o que voce precisa fazer
	$('#modal').off('hidden.bs.modal').on('hidden.bs.modal', function (e) {
		$('#collapseFalecimento').collapse('hide');
	});	

	document.querySelector('#retrato').src = 'dist/images/dubladores/retrato.png';
	document.querySelector('#retrato').style.filter = '';
	
	let tituloElement = document.querySelector('#titulo');
	if (tituloElement) {
		tituloElement.textContent = 'Editar';
	} 
	
	//O método closest() é um método JavaScript utilizado para encontrar o ancestral mais próximo de um elemento que corresponda a um determinado seletor.
	//ele é usado para encontrar a linha (<tr>) da tabela que contém o botão (btn) que foi clicado:
	// por isso que ele retorna é um tr <tr>
	
	tr = btn.closest('tr');
	
	let tds = tr.children;
	console.log(tds[0].textContent.trim());
	console.log(tds[1].textContent.trim());
	console.log(tds[2].textContent.trim());
	
	//<tr data-info='{{ {"dublador": dublador} | json_encode }}'>
	let dataInfo        = tr.dataset.info;

	//JSON.parse(dataInfo) é o json_decode 
	//<tr data-info="{&quot;dublador&quot;:{&quot;id&quot;:11,&quot;nome&quot;:&quot;Cassius Romero&quot;,&quot;data_nascimento&quot;:&quot;2023-01-01&quot;,&quot;data_falecimento&quot;:&quot;2020-10-05&quot;,&quot;imagem&quot;:&quot;Cassius Romero.jpg&quot;,&quot;sexo&quot;:&quot;M&quot;}}" role="row" class="odd">										
	let info            = JSON.parse(dataInfo);
	
	
	let id              = info.dublador.id;
	let nome            = info.dublador.nome;
	let sexo            = info.dublador.sexo;
	let imagem          = info.dublador.imagem;
	let data_nascimento = info.dublador.data_nascimento;
	let data_falecimento  = (info.dublador.data_falecimento === null || info.dublador.data_falecimento === 'null') ? '' : info.dublador.data_falecimento;

	$('#modal').off('shown.bs.modal').on('shown.bs.modal', function (e) {
		if (data_falecimento !== null && data_falecimento !== 'null' && data_falecimento.length != 0) {
			document.querySelector('input[name="data_falecimento"]').value = data_falecimento;
			$('#collapseFalecimento').collapse('show');
		} else {
			document.querySelector('input[name="data_falecimento"]').value = '';
		}
	});

	////localStorage é um jeito de salvar os dados no navegador do usuario e pegar usando javascript setItem(), getItem() , removeItem() e clear()
	//estou salvando os dados que pegamos do banco que estavam na tabela na linha <tr> para comparar depois no submit se teve alguma alteração no formulario
	localStorage.setItem('nome', nome);
	localStorage.setItem('sexo', sexo);
	localStorage.setItem('data_nascimento', data_nascimento);
	localStorage.setItem('data_falecimento', data_falecimento);
	
	//preenchendo os dados do formulario
	document.querySelector('input[name="id"]').value = id;
	document.querySelector('input[name="nome"]').value = nome;
	//aqui aprendeu essa dica nova?
	document.querySelector('input[name="sexo"][value="' + sexo + '"]').checked = true;
	document.querySelector('input[name="data_nascimento"]').value = data_nascimento;
	
	//verifica se tem imagem
	if(imagem) {
		document.querySelector('#retrato').src = 'dist/images/dubladores/' + imagem;
	
		if(data_falecimento) {	
			document.querySelector('#retrato').style.filter = 'grayscale(100%)';
		}
	}
	
	//abre a modal ja com o formulario preenchido para edição
	$('#modal').modal();	
}

function btnDeletarClick(event) {
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
		if(nomeOrig === nome && sexoOrig === sexo && data_nascimentoOrig === data_nascimento && 
			data_falecimentoOrig === data_falecimento && imagem.size === 0) {
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
