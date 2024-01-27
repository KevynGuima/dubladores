<?php
declare(strict_types=1);

namespace App\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Seeder extends Command
{
    // Adicione uma propriedade para armazenar o contêiner.
    private ContainerInterface $container;
    private $conn;
    private $logger;

    // Injete o contêiner no construtor
    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->conn   = $container->get('db');
				$this->logger = $container->get('logger');
    }

    protected function configure(): void
    {
        $this->setName('create:seeders')->setDescription('Alimenta o banco de dados com informações ficticias');
    }

	protected function execute(InputInterface $input, OutputInterface $output): int
    {
		$helper   = $this->getHelper('question');
		$question = new Question('<question>Deseja alimentar o banco de dados com dados fakes? (S/N):</question>');

		$question->setValidator(function ($answer) {
			if (!in_array(strtoupper($answer), ['S', 'N'])) {
				throw new \RuntimeException('Resposta inválida. Por favor, digite S ou N.');
			}

			return $answer;
		});

		$answer = $helper->ask($input, $output, $question);
		
		if (strtoupper($answer) === 'S') {
			$this->conn->executeStatement('SET FOREIGN_KEY_CHECKS=0');
		
			$this->conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
			
			$sm     = $this->conn->getSchemaManager();
			$tables = $sm->listTables();

			foreach ($tables as $table) {
				$SQLString = 'TRUNCATE TABLE '. $table->getName();
				$this->conn->executeStatement($SQLString);
				$output->writeln("<comment>$SQLString!</comment>");
			}

			$this->conn->executeStatement('SET FOREIGN_KEY_CHECKS=1');
			
			$queryBuilder = $this->conn->createQueryBuilder();

//######### Seeder Generos #########################################################################################################################
			$generos = [
				'Ação',
				'Animação',
				'Aventura',
				'Comédia',
				'Crime',
				'Documentário',
				'Drama',
				'Espionagem',
				'Faroeste',
				'Fantasia',
				'Ficção Científica',
				'Guerra',
				'Musical',
				'Policial',
				'Romance',
				'Suspense',
				'Terror'
			];

			$i = 0;
			foreach ($generos as $genero) {
				// Constrói a inserção na tabela 'generos'
				$queryBuilder
					->insert('generos')
					->values([
						'genero' => $queryBuilder->createNamedParameter($genero)
					]);

				// Executa a consulta
				$queryBuilder->execute();

				// Obtém o ID gerado para a categoria inserida
				$id = $this->conn->lastInsertId();

				$i++;
			}
			
			$output->writeln("<info>Criado $i generos com sucesso!</info>");

//######### Tabela Filmes #########################################################################################################################
			$filmes = [
				'O Labirinto do Fauno',
				'Clube da Luta',
				'A Origem',
				'Efeito Borboleta',
				'Cidade de Deus',
				'A Viagem de Chihiro',
				'Interestelar',
				'Pulp Fiction',
				'O Senhor dos Anéis: A Sociedade do Anel',
				'Matrix',
				'Crepúsculo dos Deuses',
				'Cidadão Kane',
				'O Iluminado',
				'O Poderoso Chefão',
				'A Lista de Schindler',
				'O Grande Gatsby',
				'Titanic',
				'O Rei Leão',
				'Jurassic Park',
				'Forrest Gump',
				'O Sexto Sentido',
				'O Artista',
				'La La Land',
				'Pantera Negra',
				'Avatar',
				'A Forma da Água',
				'Mad Max: Estrada da Fúria',
				'Birdman',
				'Moulin Rouge!',
				'O Show de Truman'
			];

			$i = 0;
			foreach($filmes as $filme) {
				$queryBuilder
					->insert('filmes')
					->values([
						'nome' => $queryBuilder->createNamedParameter($filme),
						'data_lancamento' => $queryBuilder->createNamedParameter((new \DateTime('2023-01-01'))->format('Y-m-d')),
						'imagem' => $queryBuilder->createNamedParameter("caminho/$filme.jpg")
					]);

				$queryBuilder->execute();
				$filme_id = $this->conn->lastInsertId();			
				
				$rand = rand(1, 4);
				
				$genero = [];
				$sql  = 'SELECT id FROM generos ORDER BY RAND() LIMIT ' . $rand;
				
				//try {				
				$stmt       = $this->conn->executeQuery($sql);
				$result     = $stmt->fetchAllNumeric();
				$genero_ids = array_column($result, 0);
				
				foreach ($genero_ids as $genero_id) {
					$queryBuilder
						->insert('filme_genero')
						->values([
							'filme_id' => $queryBuilder->createNamedParameter($filme_id, 'integer'),
							'genero_id' => $queryBuilder->createNamedParameter($genero_id, 'integer')
						]);

					$result = $queryBuilder->executeQuery();
					if($result->rowCount() > 0) {
						$result = null;
					}
				}
				$i++;
			}
			
			$output->writeln("<info>Criado $i filmes com sucesso!</info>");
			
//######### Tabela series #########################################################################################################################			
			$series = [
				'Stranger Things',
				'Game of Thrones',
				'Breaking Bad',
				'The Mandalorian',
				'Friends',
				'The Witcher',
				'The Crown',
				'Black Mirror',
				'Narcos',
				'Westworld',
				'The Office',
				'Mindhunter',
				'A Casa de Papel',
				'The Umbrella Academy',
				'The Simpsons',
				'Peaky Blinders',
				'Fargo',
				'Sherlock',
				'The Big Bang Theory',
				'Agentes Shields'
			];
			
			$i = 0;
			foreach($series as $serie) {
				$queryBuilder
						->insert('series')
						->values([
							'nome' => $queryBuilder->createNamedParameter($filme),
							'data_lancamento' => $queryBuilder->createNamedParameter((new \DateTime('2023-01-01'))->format('Y-m-d')),
							'temporadas' => rand(1, 10),
							'imagem' => $queryBuilder->createNamedParameter("caminho/$serie.jpg")
						]);

					$queryBuilder->execute();
					$serie_id = $this->conn->lastInsertId();			
					
					$rand = rand(1, 4);
					
					$genero = [];
					$sql  = 'SELECT id FROM generos ORDER BY RAND() LIMIT ' . $rand;
					
					//try {				
					$stmt       = $this->conn->executeQuery($sql);
					$result     = $stmt->fetchAllNumeric();
					$genero_ids = array_column($result, 0);
				
				
				foreach ($genero_ids as $genero_id) {
					$queryBuilder
						->insert('genero_serie')
						->values([							
							'genero_id' => $queryBuilder->createNamedParameter($genero_id, 'integer'),
							'serie_id' => $queryBuilder->createNamedParameter($serie_id, 'integer')
						]);

					$result = $queryBuilder->executeQuery();
					if($result->rowCount() > 0) {
						$result = null;
					}
				}
				$i++;
			}
			
			$output->writeln("<info>Criado $i series com sucesso!</info>");

//######### Tabela animes #########################################################################################################################	

			$animes = [
				'Naruto',
				'One Piece',
				'Dragon Ball Z',
				'Attack on Titan',
				'My Hero Academia',
				'Death Note',
				'Fullmetal Alchemist: Brotherhood',
				'Tokyo Ghoul',
				'Demon Slayer',
				'Sword Art Online',
				'Hunter x Hunter',
				'One Punch Man',
				'Steins;Gate',
				'Cowboy Bebop',
				'Neon Genesis Evangelion',
				'Code Geass',
				'Fairy Tail',
				'Bleach',
				'JoJo\'s Bizarre Adventure',
				'Black Clover',
				'Haikyuu!!',
				'The Promised Neverland',
				'Naruto: Shippuden',
				'Kimetsu no Yaiba',
				'Mob Psycho 100',
				'Re:Zero',
				'Death Parade',
				'Parasyte: The Maxim',
				'Akira'
			];

			$i = 0;
			foreach($animes as $anime) {
				$queryBuilder
					->insert('animes')
					->values([
						'nome'            => $queryBuilder->createNamedParameter($anime),
						'data_lancamento' => $queryBuilder->createNamedParameter((new \DateTime('2023-01-01'))->format('Y-m-d')),
						'temporadas'      => rand(1, 20),
						'imagem'          => $queryBuilder->createNamedParameter("$anime.jpg")
					]);

				$queryBuilder->execute();
				$anime_id = $this->conn->lastInsertId();			
					
				$rand = rand(1, 8);
				
				$genero = [];
				$sql  = 'SELECT id FROM generos ORDER BY RAND() LIMIT ' . $rand;
				
				$stmt       = $this->conn->executeQuery($sql);
				$result     = $stmt->fetchAllNumeric();
				$generos_ids = array_column($result, 0);				
				
				foreach ($generos_ids as $genero_id) {
					$queryBuilder
						->insert('anime_genero')
						->values([							
							'anime_id' => $queryBuilder->createNamedParameter($anime_id, 'integer'),
							'genero_id' => $queryBuilder->createNamedParameter($genero_id, 'integer')
						]);

					$result = $queryBuilder->executeQuery();
					if($result->rowCount() > 0) {
						$result = null;
					}
				}
				$i++;
			}
			
			$output->writeln("<info>Criado $i animes com sucesso!</info>");
			
//######### Tabela dubladores #########################################################################################################################	

			$dubladores = [
				'Wendel Bezerra',
				'Márcia Regina',
				'Garcia Júnior',
				'Melissa Garcia',
				'Fábio Lucindo',
				'Tatiane Keplmair',
				'Isaac Bardavid',
				'Gutemberg Barros',
				'Letícia Quinto',
				'Mauro Ramos',
				'Cassius Romero',
				'Guilherme Briggs',
				'Cecília Lemes',
				'Fátima Noya',
				'Nair Silva',
				'Júlio Chaves',
				'Miriam Ficher',
				'Marco Antônio Costa',
				'Jorgeh Ramos',
				'Jussara Marques',
				'Samira Fernandes',
				'Élcio Sodré',
				'Zeca Rodrigues',
				'Selton Mello',
				'Reginaldo Primo',
				'Fernanda Crispim',
				'Fernanda Baronne',
				'Vagner Fagundes',
				'Priscila Amorim'
			];

			$i = 0;
			$sexo = '';
			foreach($dubladores as $dublador) {
				$primeiraPalavra = strtok($dublador, ' ');

				// Verificar se a primeira palavra termina com 'a' ou 'o'
				if (substr($primeiraPalavra, -1) === 'a') {
					$sexo = 'F';
				} elseif (substr($primeiraPalavra, -1) === 'o') {
					$sexo = 'M';
				} else {
					// Atribuir 'f' se o número for 0, caso contrário, atribuir 'm'
					$sexo = (rand(0, 1) === 0) ? 'F' : 'M';
				}

				$queryBuilder
					->insert('dubladores')
					->values([
						'nome'            => $queryBuilder->createNamedParameter($dublador),
						'data_nascimento' => $queryBuilder->createNamedParameter((new \DateTime('2023-01-01'))->format('Y-m-d')),						
						'imagem'          => $queryBuilder->createNamedParameter("$dublador.jpg"),
						'sexo'            => $queryBuilder->createNamedParameter($sexo)
					]);

				$queryBuilder->execute();
				$dublador_id = $this->conn->lastInsertId();			
				
				//filmes				
				$rand = rand(1, 8);
				
				$sql       = 'SELECT id FROM filmes ORDER BY RAND() LIMIT ' . $rand;				
				$stmt      = $this->conn->executeQuery($sql);
				$result    = $stmt->fetchAllNumeric();
				$filme_ids = array_column($result, 0);				
				
				foreach ($filme_ids as $filme_id) {
					$queryBuilder
						->insert('dublador_filme')
						->values([							
							'dublador_id' => $queryBuilder->createNamedParameter($dublador_id, 'integer'),
							'filme_id' => $queryBuilder->createNamedParameter($filme_id, 'integer'),
							'interprete' => $queryBuilder->createNamedParameter('desenho', 'string'),
							'personagem' => $queryBuilder->createNamedParameter('desenho', 'string'),
							'midia' => $queryBuilder->createNamedParameter("audio_$dublador_id.mp3")
						]);

					$result = $queryBuilder->executeQuery();
					if($result->rowCount() > 0) {
						$result = null;
					}
				}
				
				//animes
				$rand = rand(1, 15);
				
				$sql        = 'SELECT id FROM animes ORDER BY RAND() LIMIT ' . $rand;				
				$stmt       = $this->conn->executeQuery($sql);
				$result     = $stmt->fetchAllNumeric();
				$animes_ids = array_column($result, 0);				
				
				foreach ($animes_ids as $anime_id) {
					$queryBuilder
						->insert('anime_dublador')
						->values([							
							'anime_id' => $queryBuilder->createNamedParameter($anime_id, 'integer'),
							'dublador_id' => $queryBuilder->createNamedParameter($dublador_id, 'integer'),							
							'interprete' => $queryBuilder->createNamedParameter('desenho', 'string'),
							'personagem' => $queryBuilder->createNamedParameter('desenho', 'string'),
							'midia' => $queryBuilder->createNamedParameter("audio_$dublador_id.mp3")
						]);

					$result = $queryBuilder->executeQuery();
					if($result->rowCount() > 0) {
						$result = null;
					}
				}

				//series
				$rand = rand(1, 12);
				
				$sql  = 'SELECT id FROM series ORDER BY RAND() LIMIT ' . $rand;
				
				$stmt       = $this->conn->executeQuery($sql);
				$result     = $stmt->fetchAllNumeric();
				$series_ids = array_column($result, 0);				
				
				foreach ($series_ids as $serie_id) {
					$queryBuilder
						->insert('dublador_serie')
						->values([														
							'dublador_id' => $queryBuilder->createNamedParameter($dublador_id, 'integer'),							
							'serie_id' => $queryBuilder->createNamedParameter($serie_id, 'integer'),
							'interprete' => $queryBuilder->createNamedParameter('desenho', 'string'),
							'personagem' => $queryBuilder->createNamedParameter('desenho', 'string'),
							'midia' => $queryBuilder->createNamedParameter("audio_$dublador_id.mp3")
						]);

					$result = $queryBuilder->executeQuery();
					if($result->rowCount() > 0) {
						$result = null;
					}
				}
				
				$i++;
			}
			
			$output->writeln("<info>Criado $i dubladores com sucesso!</info>");
			
//######### Tabela usuarios #########################################################################################################################				
			
		$nomes = [
			[
				'nome' => 'João Silva',
				'email' => 'joao.silva@example.com',
				'senha' => 'senha123',
				'data_nascimento' => '1990-05-15',
			],
			[
				'nome' => 'Ana Oliveira',
				'email' => 'ana.oliveira@example.com',
				'senha' => 'senha456',
				'data_nascimento' => '1985-09-22',
			],
			[
				'nome' => 'Carlos Santos',
				'email' => 'carlos.santos@example.com',
				'senha' => 'senha789',
				'data_nascimento' => '1988-12-10',
			],
			[
				'nome' => 'Mariana Costa',
				'email' => 'mariana.costa@example.com',
				'senha' => 'senha987',
				'data_nascimento' => '1992-04-30',
			],
			[
				'nome' => 'Lucas Oliveira',
				'email' => 'lucas.oliveira@example.com',
				'senha' => 'senha567',
				'data_nascimento' => '1987-07-18',
			],
			[
				'nome' => 'Camila Lima',
				'email' => 'camila.lima@example.com',
				'senha' => 'senha321',
				'data_nascimento' => '1995-02-25',
			],
			[
				'nome' => 'Pedro Rocha',
				'email' => 'pedro.rocha@example.com',
				'senha' => 'senha654',
				'data_nascimento' => '1993-11-12',
			],
			[
				'nome' => 'Fernanda Martins',
				'email' => 'fernanda.martins@example.com',
				'senha' => 'senha234',
				'data_nascimento' => '1989-08-08',
			],
			[
				'nome' => 'Rafael Souza',
				'email' => 'rafael.souza@example.com',
				'senha' => 'senha876',
				'data_nascimento' => '1994-06-03',
			],
			[
				'nome' => 'Juliana Pereira',
				'email' => 'juliana.pereira@example.com',
				'senha' => 'senha789',
				'data_nascimento' => '1986-03-20',
			]
		];

		$i = 0;
		foreach ($nomes as $pessoa) {
			$senhaCriptografada = password_hash($pessoa['senha'], PASSWORD_BCRYPT);
			$tokenValidacao     = bin2hex(random_bytes(32));

			$queryBuilder
				->insert('usuarios')
				->values([
					'nome' => ':nome',
					'email' => ':email',
					'senha' => ':senha',
					'token_validacao' => ':token_validacao',
					'data_nascimento' => ':data_nascimento'
				])
				->setParameter('nome', $pessoa['nome'])
				->setParameter('email', $pessoa['email'])
				->setParameter('senha', $senhaCriptografada)
				->setParameter('token_validacao', $tokenValidacao)
				->setParameter('data_nascimento', $pessoa['data_nascimento']);
				

			// Executar a consulta
			$queryBuilder->execute();
			$i++;
		}
			
		$output->writeln("<info>Criado $i usuarios com sucesso!</info>");		

//######### Tabela tarefas #########################################################################################################################				
		$tarefas = [
			[
				'tarefa' => 'Generos - arrumar a mensagem quando se digita apenas 3 caracters (traduzir)',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Generos - na edição está permitindo 3 caracters',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Generos - acentuação na mensagem "esse gênero já existe"',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Generos - não permitir apagar o gênero se ele estiver sendo usado',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Animes - não está funcionando nenhuma ação',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Animes - não permitir animes com o mesmo nome e data de lançamento',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Filmes - não permitir cadastrar nomes de filmes menores que 4 caracters',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Filmes - não está editando a foto',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Filmes - não está salvando a foto na pasta certa',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Filmes - não permitir filmes com o mesmo nome e data de lançamento',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Séries - não permitir séries com o mesmo nome e data de lançamento',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Séries - não está salvando a foto na pasta certa',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Séries - não está editando a foto',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Dubladores - não está salvando',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Dubladores - não está editando',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Dubladores - não está mostrando a foto padrão quando está vazio',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Dubladores - não está excluindo',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Usuarios - arrumar cadastro de usuários e mensagens de erro',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Usuarios - não deixar salvar data de nascimento menor que 15 anos',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Usuarios - senha no minimo com 6 caracters',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Usuarios - arrumar o botão deletar',
				'concluido' => 'N'
			],
			[
				'tarefa' => 'Usuarios - não permitir apagar usuário logado',
				'concluido' => 'N'
			]
		];

		$i = 0;
		foreach ($tarefas as $tarefa) {
			$queryBuilder
				->insert('tarefas')
				->values([
					'tarefa' => ':tarefa',
					'concluido' => ':concluido'
				])
				->setParameter('tarefa', $tarefa['tarefa'])
				->setParameter('concluido', $tarefa['concluido']);				

			// Executar a consulta
			$queryBuilder->execute();
			$i++;
		}
			
		$output->writeln("<info>Criado $i tarefas com sucesso!</info>");
			
		} else {
			$output->writeln('<comment>Nada foi feito.</comment>');
		}
	
        return Command::SUCCESS;
    }
}