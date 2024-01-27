document.addEventListener('DOMContentLoaded', () => {
	// Pega a URL atual
	let currentUrl = window.location.href;

	// Seleciona todos os links no menu
	let menuLinks = document.querySelectorAll('ul.treeview-menu li a');

	// Itera sobre os links e verifica se a URL corresponde
	for (let i = 0; i < menuLinks.length; i++) {
		let link = menuLinks[i];
		
		// Compara se a URL atual termina com o caminho relativo do link
		if (currentUrl.endsWith(link.getAttribute('href'))) {
			// Adiciona a classe "active" ao link correspondente
			link.parentNode.classList.add('active');
			
			// Adiciona a classe "menu-open" ao avô (treeview) correspondente
			let treeview = findAncestor(link.parentNode, 'treeview');
			if (treeview) {
				treeview.classList.add('menu-open');
				
				// Adiciona o estilo display: block; à tag <ul class="treeview-menu">
				let treeviewMenu = treeview.querySelector('.treeview-menu');
				if (treeviewMenu) {
					treeviewMenu.style.display = 'block';
				}
			}
		}
	}
	
	// Função para encontrar o ancestral com uma classe específica
	function findAncestor(element, className) {
		while ((element = element.parentElement) && !element.classList.contains(className));
		return element;
	}

	let checkboxes = document.querySelectorAll('.check');

	checkboxes.forEach((checkbox) => {
		checkbox.addEventListener('change', () => {
			const id = checkbox.id;
			let concluido;

			let label = checkbox.closest('li').querySelector('.text label');
			
			if (checkbox.checked) {
				label.style.textDecoration = 'line-through';
				concluido = 'S';
			} else {
				label.style.textDecoration = 'none';
				concluido = 'N';
			}
			
			const formData = new FormData();
			formData.append('id', id);
			formData.append('concluido', concluido);
			
			let requestOptions = {
				method: 'POST',
				body: formData
			};
			
			fetch('insert', requestOptions)
			.then(response => {
				if (response.status === 200) {
					Swal.fire({
					  position: 'top-end',
					  icon: 'success',
					  title: 'Feito com sucesso',
					  showConfirmButton: false,
					  timer: 1000
					});
				}				
			});
	  
		});
	});
	
	let editIcons = document.querySelectorAll('.fa-edit');
	let deleteIcons = document.querySelectorAll('.fa-trash-o');
	
	  editIcons.forEach(function(icon) {
		icon.addEventListener('click', function() {
		  let id = icon.getAttribute('data-id');
		  console.log('edit ' + id);
		  //enviarDadosParaController('/seu/controlador/editar', id, true);
		});
	  });

	  deleteIcons.forEach(function(icon) {
		icon.addEventListener('click', function() {
		  let id = icon.getAttribute('data-id');
		  console.log(id);
		  //enviarDadosParaController('/seu/controlador/deletar', id, true);
		});
	  });	
});