<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Question\Question;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;

class Table extends Command
{
    // Adicione uma propriedade para armazenar o contêiner
    private ContainerInterface $container;

    // Injete o contêiner no construtor
    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure(): void
    {
        $this->setName('create:tables')->setDescription('Cria todas as tabelas');
    }

	protected function execute(InputInterface $input, OutputInterface $output): int
    {
		$helper   = $this->getHelper('question');
		$question = new Question('<question>Deseja criar as tabelas? (S/N):</question>');


		$question->setValidator(function ($answer) {
			if (!in_array(strtoupper($answer), ['S', 'N'])) {
				throw new \RuntimeException('Resposta inválida. Por favor, digite S ou N.');
			}

			return $answer;
		});

		$answer = $helper->ask($input, $output, $question);
		
		if (strtoupper($answer) === 'S') {
			$conn   = $this->container->get('db');
			$params = $conn->getParams();

			// Desativa temporariamente as verificações de chave estrangeira
			$conn->executeStatement('SET FOREIGN_KEY_CHECKS=0');
			
			$schemaManager = $conn->getSchemaManager();

//######### Tabela Usuarios #########################################################################################################################
			$tableName = 'usuarios';

			// Verifica se a tabela existe
			if ($schemaManager->tablesExist([$tableName])) {
				$schemaManager->dropTable($tableName);
				$output->writeln("<comment>Tabela [$tableName] apagada com sucesso!</comment>");
			}        

			$schema = new Schema();
			$table = $schema->createTable($tableName);			
			$table->addOption('charset', $params['charset']);
			$table->addOption('collate', $params['collation']);			
			$table->addColumn('id', Types::SMALLINT, ['unsigned' => true, 'autoincrement' => true]);
			$table->addColumn('nome', Types::STRING, ['length' => 50]);
			$table->addColumn('email', Types::STRING, ['length' => 64]);
			$table->addColumn('senha', Types::STRING, ['fixed' => true, 'length' => 60]);
			$table->addColumn('token_validacao', Types::STRING, ['fixed' => true, 'length' => 64]);
			$table->addColumn('token_lembrar_senha', Types::STRING, ['fixed' => true, 'length' => 64, 'notnull' => false]);
			$table->addColumn('data_nascimento', Types::DATE_MUTABLE, ['notnull' => false]);
			$table->addColumn('ultimo_acesso', Types::DATETIME_MUTABLE, [
				'default' => 'CURRENT_TIMESTAMP',
				'notnull' => false,
				'columnDefinition' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
			]);
			$table->addUniqueConstraint(['email']);
			$table->setPrimaryKey(['id']);
			
			try {
				// Obtém a instrução SQL para criar a tabela
				$sql = $schema->toSql($conn->getDatabasePlatform());

				// Executa a instrução SQL
				foreach ($sql as $sqlStatement) {
					$conn->executeStatement($sqlStatement);
				}

				$conn->executeStatement("ALTER TABLE $tableName ADD COLUMN `acesso` enum('0', '1', '2') DEFAULT '0' COMMENT '0=desativado,1=normal,2=admin'");

				$output->writeln("<info>Tabela [$tableName] criada com sucesso!</info>");
			} catch (SchemaException $e) {
				$output->writeln('<error>Erro ao criar a tabela: ' . $e->getMessage() .'</error>');
			}

//######### Tabela Dubladores #########################################################################################################################
			$tableName = 'dubladores';

			// Verifica se a tabela existe
			if ($schemaManager->tablesExist([$tableName])) {
				$schemaManager->dropTable($tableName);
				$output->writeln("<comment>Tabela [$tableName] apagada com sucesso!</comment>");
			}        

			$schema = new Schema();
			$table = $schema->createTable($tableName);			
			$table->addOption('charset', $params['charset']);
			$table->addOption('collate', $params['collation']);
			
			$table->addColumn('id', Types::SMALLINT, ['unsigned' => true, 'autoincrement' => true]);
			$table->addColumn('nome', Types::STRING, ['length' => 60]);
			$table->addColumn('data_nascimento', Types::DATE_IMMUTABLE);
			$table->addColumn('data_falecimento', Types::DATE_IMMUTABLE, ['comment' => 'Quando existir data é porque ja faleceu', 'notnull' => false, 'default' => null]);
			$table->addColumn('imagem', Types::STRING, ['length' => 100]);
			$table->setPrimaryKey(['id']);

			try {
				// Obtém a instrução SQL para criar a tabela
				$sql = $schema->toSql($conn->getDatabasePlatform());

				// Executa a instrução SQL
				foreach ($sql as $sqlStatement) {
					$conn->executeStatement($sqlStatement);
				}

				$conn->executeStatement("ALTER TABLE $tableName ADD COLUMN sexo ENUM('M', 'F') DEFAULT 'M'");

				$output->writeln("<info>Tabela [$tableName] criada com sucesso!</info>");
			} catch (SchemaException $e) {
				$output->writeln('<error>Erro ao criar a tabela: ' . $e->getMessage() .'</error>');
			}

//######### Tabela Generos #########################################################################################################################
			$tableName = 'generos';

			// Verifica se a tabela existe
			if ($schemaManager->tablesExist([$tableName])) {
				$schemaManager->dropTable($tableName);
				$output->writeln("<comment>Tabela [$tableName] apagada com sucesso!</comment>");
			}        

			$schema = new Schema();
			$table = $schema->createTable($tableName);			
			$table->addOption('charset', $params['charset']);
			$table->addOption('collate', $params['collation']);
			
			$table->addColumn('id', Types::SMALLINT, ['unsigned' => true, 'autoincrement' => true]);
			$table->addColumn('genero', Types::STRING, ['length' => 30]);
			$table->addUniqueConstraint(['genero']);
			$table->setPrimaryKey(['id']);

			try {
				// Obtém a instrução SQL para criar a tabela
				$sql = $schema->toSql($conn->getDatabasePlatform());

				// Executa a instrução SQL
				foreach ($sql as $sqlStatement) {
					$conn->executeStatement($sqlStatement);
				}

				$output->writeln("<info>Tabela [$tableName] criada com sucesso!</info>");
			} catch (SchemaException $e) {
				$output->writeln('<error>Erro ao criar a tabela: ' . $e->getMessage() .'</error>');
			}

//######### Tabela Filmes #########################################################################################################################
			$tableName = 'filmes';

			// Verifica se a tabela existe
			if ($schemaManager->tablesExist([$tableName])) {
				$schemaManager->dropTable($tableName);
				$output->writeln("<comment>Tabela [$tableName] apagada com sucesso!</comment>");
			}        

			$schema = new Schema();
			$table = $schema->createTable($tableName);			
			$table->addOption('charset', $params['charset']);
			$table->addOption('collate', $params['collation']);			
			$table->addColumn('id', Types::SMALLINT, ['unsigned' => true, 'autoincrement' => true]);
			$table->addColumn('nome', Types::STRING, ['length' => 50]);
			$table->addColumn('data_lancamento', Types::DATE_MUTABLE);
			$table->addColumn('imagem', Types::STRING, ['length' => 100]);
			$table->setPrimaryKey(['id']);

			try {
				// Obtém a instrução SQL para criar a tabela
				$sql = $schema->toSql($conn->getDatabasePlatform());

				// Executa a instrução SQL
				foreach ($sql as $sqlStatement) {
					$conn->executeStatement($sqlStatement);
				}

				$output->writeln("<info>Tabela [$tableName] criada com sucesso!</info>");
			} catch (SchemaException $e) {
				$output->writeln('<error>Erro ao criar a tabela: ' . $e->getMessage() .'</error>');
			}

//######### Tabela Series #########################################################################################################################
			$tableName = 'series';

			// Verifica se a tabela existe
			if ($schemaManager->tablesExist([$tableName])) {
				$schemaManager->dropTable($tableName);
				$output->writeln("<comment>Tabela [$tableName] apagada com sucesso!</comment>");
			}        

			$schema = new Schema();
			$table = $schema->createTable($tableName);			
			$table->addOption('charset', $params['charset']);
			$table->addOption('collate', $params['collation']);
			
			$table->addColumn('id', Types::SMALLINT, ['unsigned' => true, 'autoincrement' => true]);
			$table->addColumn('nome', Types::STRING, ['length' => 50]);
			$table->addColumn('data_lancamento', Types::DATE_IMMUTABLE);
			$table->addColumn('imagem', Types::STRING, ['length' => 100]);
			$table->addColumn('temporadas', Types::SMALLINT);
			$table->setPrimaryKey(['id']);

			try {
				// Obtém a instrução SQL para criar a tabela
				$sql = $schema->toSql($conn->getDatabasePlatform());

				// Executa a instrução SQL
				foreach ($sql as $sqlStatement) {
					$conn->executeStatement($sqlStatement);
				}

				$output->writeln("<info>Tabela [$tableName] criada com sucesso!</info>");
			} catch (SchemaException $e) {
				$output->writeln('<error>Erro ao criar a tabela: ' . $e->getMessage() .'</error>');
			}
			
//######### Tabela Animes #########################################################################################################################
			$tableName = 'animes';

			// Verifica se a tabela existe
			if ($schemaManager->tablesExist([$tableName])) {
				$schemaManager->dropTable($tableName);
				$output->writeln("<comment>Tabela [$tableName] apagada com sucesso!</comment>");
			}        

			$schema = new Schema();
			$table = $schema->createTable($tableName);			
			$table->addOption('charset', $params['charset']);
			$table->addOption('collate', $params['collation']);			
			$table->addColumn('id', Types::SMALLINT, ['unsigned' => true, 'autoincrement' => true]);
			$table->addColumn('nome', Types::STRING, ['length' => 50]);
			$table->addColumn('data_lancamento', Types::DATE_IMMUTABLE);
			$table->addColumn('imagem', Types::STRING, ['length' => 100]);
			$table->addColumn('temporadas', Types::SMALLINT);
			$table->setPrimaryKey(['id']);

			try {
				// Obtém a instrução SQL para criar a tabela
				$sql = $schema->toSql($conn->getDatabasePlatform());

				// Executa a instrução SQL
				foreach ($sql as $sqlStatement) {
					$conn->executeStatement($sqlStatement);
				}

				$output->writeln("<info>Tabela [$tableName] criada com sucesso!</info>");
			} catch (SchemaException $e) {
				$output->writeln('<error>Erro ao criar a tabela: ' . $e->getMessage() .'</error>');
			}			

//######### Tabela filme_genero #########################################################################################################################
			$tableName = 'filme_genero';

			// Verifica se a tabela existe
			if ($schemaManager->tablesExist([$tableName])) {
				$schemaManager->dropTable($tableName);
				$output->writeln("<comment>Tabela [$tableName] apagada com sucesso!</comment>");
			}        

			$schema = new Schema();
			$table = $schema->createTable($tableName);			
			$table->addOption('charset', $params['charset']);
			$table->addOption('collate', $params['collation']);			
			$table->addColumn('filme_id', Types::SMALLINT, ['unsigned' => true]);
			$table->addColumn('genero_id', Types::SMALLINT, ['unsigned' => true]);			
			$table->addForeignKeyConstraint('filmes', ['filme_id'], ['id'], ['onDelete' => 'CASCADE']);
			$table->addForeignKeyConstraint('generos', ['genero_id'], ['id'], ['onDelete' => 'CASCADE']);
			$table->setPrimaryKey(['filme_id', 'genero_id']);

			try {
				// Obtém a instrução SQL para criar a tabela
				$sql = $schema->toSql($conn->getDatabasePlatform());

				// Executa a instrução SQL
				foreach ($sql as $sqlStatement) {
					$conn->executeStatement($sqlStatement);
				}

				$output->writeln("<info>Tabela [$tableName] criada com sucesso!</info>");
			} catch (SchemaException $e) {
				$output->writeln('<error>Erro ao criar a tabela: ' . $e->getMessage() .'</error>');
			}
			
//######### Tabela genero_serie #########################################################################################################################
			$tableName = 'genero_serie';

			// Verifica se a tabela existe
			if ($schemaManager->tablesExist([$tableName])) {
				$schemaManager->dropTable($tableName);
				$output->writeln("<comment>Tabela [$tableName] apagada com sucesso!</comment>");
			}        

			$schema = new Schema();
			$table = $schema->createTable($tableName);			
			$table->addOption('charset', $params['charset']);
			$table->addOption('collate', $params['collation']);			
			$table->addColumn('genero_id', Types::SMALLINT, ['unsigned' => true]);
			$table->addColumn('serie_id', Types::SMALLINT, ['unsigned' => true]);
			$table->addForeignKeyConstraint('generos', ['genero_id'], ['id'], ['onDelete' => 'CASCADE']);
			$table->addForeignKeyConstraint('series', ['serie_id'], ['id'], ['onDelete' => 'CASCADE']);
			$table->setPrimaryKey(['genero_id', 'serie_id']);

			try {
				// Obtém a instrução SQL para criar a tabela
				$sql = $schema->toSql($conn->getDatabasePlatform());

				// Executa a instrução SQL
				foreach ($sql as $sqlStatement) {
					$conn->executeStatement($sqlStatement);
				}

				$output->writeln("<info>Tabela [$tableName] criada com sucesso!</info>");
			} catch (SchemaException $e) {
				$output->writeln('<error>Erro ao criar a tabela: ' . $e->getMessage() .'</error>');
			}

//######### Tabela anime_genero #########################################################################################################################
			$tableName = 'anime_genero';

			// Verifica se a tabela existe
			if ($schemaManager->tablesExist([$tableName])) {
				$schemaManager->dropTable($tableName);
				$output->writeln("<comment>Tabela [$tableName] apagada com sucesso!</comment>");
			}        

			$schema = new Schema();
			$table = $schema->createTable($tableName);			
			$table->addOption('charset', $params['charset']);
			$table->addOption('collate', $params['collation']);			
			$table->addColumn('anime_id', Types::SMALLINT, ['unsigned' => true]);
			$table->addColumn('genero_id', Types::SMALLINT, ['unsigned' => true]);
			$table->addForeignKeyConstraint('animes', ['anime_id'], ['id'], ['onDelete' => 'CASCADE']);
			$table->addForeignKeyConstraint('generos', ['genero_id'], ['id'], ['onDelete' => 'CASCADE']);
			$table->setPrimaryKey(['anime_id', 'genero_id']);

			try {
				// Obtém a instrução SQL para criar a tabela
				$sql = $schema->toSql($conn->getDatabasePlatform());

				// Executa a instrução SQL
				foreach ($sql as $sqlStatement) {
					$conn->executeStatement($sqlStatement);
				}

				$output->writeln("<info>Tabela [$tableName] criada com sucesso!</info>");
			} catch (SchemaException $e) {
				$output->writeln('<error>Erro ao criar a tabela: ' . $e->getMessage() .'</error>');
			}				

//######### Tabela Dublador_filme #########################################################################################################################
			$tableName = 'dublador_filme';

			// Verifica se a tabela existe
			if ($schemaManager->tablesExist([$tableName])) {
				$schemaManager->dropTable($tableName);
				$output->writeln("<comment>Tabela [$tableName] apagada com sucesso!</comment>");
			}        

			$schema = new Schema();
			$table = $schema->createTable($tableName);			
			$table->addOption('charset', $params['charset']);
			$table->addOption('collate', $params['collation']);			
			$table->addColumn('dublador_id', Types::SMALLINT, ['unsigned' => true]);
			$table->addColumn('filme_id', Types::SMALLINT, ['unsigned' => true]);
			$table->addForeignKeyConstraint('dubladores', ['dublador_id'], ['id'], ['onDelete' => 'CASCADE']);
			$table->addForeignKeyConstraint('filmes', ['filme_id'], ['id'], ['onDelete' => 'CASCADE']);
			$table->addColumn('midia', Types::STRING, ['length' => 100, 'comment' => 'Trecho de áudio ou vídeo']);
			$table->addColumn('interprete', Types::STRING, ['length' => 50]);
			$table->addColumn('personagem', Types::STRING, ['length' => 50]);
			$table->addColumn('comentario', Types::TEXT, ['length' => 65535, 'notnull' => false]);
			
			$table->setPrimaryKey(['dublador_id', 'filme_id']);

			try {
				// Obtém a instrução SQL para criar a tabela
				$sql = $schema->toSql($conn->getDatabasePlatform());

				// Executa a instrução SQL
				foreach ($sql as $sqlStatement) {
					$conn->executeStatement($sqlStatement);
				}

				$output->writeln("<info>Tabela [$tableName] criada com sucesso!</info>");
			} catch (SchemaException $e) {
				$output->writeln('<error>Erro ao criar a tabela: ' . $e->getMessage() .'</error>');
			}

//######### Tabela Dublador_serie #########################################################################################################################
			$tableName = 'dublador_serie';

			// Verifica se a tabela existe
			if ($schemaManager->tablesExist([$tableName])) {
				$schemaManager->dropTable($tableName);
				$output->writeln("<comment>Tabela [$tableName] apagada com sucesso!</comment>");
			}        

			$schema = new Schema();
			$table = $schema->createTable($tableName);			
			$table->addOption('charset', $params['charset']);
			$table->addOption('collate', $params['collation']);			
			$table->addColumn('dublador_id', Types::SMALLINT, ['unsigned' => true]);
			$table->addColumn('serie_id', Types::SMALLINT, ['unsigned' => true]);
			$table->addForeignKeyConstraint('dubladores', ['dublador_id'], ['id'], ['onDelete' => 'CASCADE']);
			$table->addForeignKeyConstraint('series', ['serie_id'], ['id'], ['onDelete' => 'CASCADE']);
			$table->addColumn('midia', Types::STRING, ['length' => 100, 'comment' => 'Trecho de áudio ou vídeo']);
			$table->addColumn('interprete', Types::STRING, ['length' => 50]);
			$table->addColumn('personagem', Types::STRING, ['length' => 50]);
			$table->addColumn('comentario', Types::TEXT, ['length' => 65535, 'notnull' => false]);

			$table->setPrimaryKey(['dublador_id', 'serie_id']);

			try {
				// Obtém a instrução SQL para criar a tabela
				$sql = $schema->toSql($conn->getDatabasePlatform());

				// Executa a instrução SQL
				foreach ($sql as $sqlStatement) {
					$conn->executeStatement($sqlStatement);
				}

				$output->writeln("<info>Tabela [$tableName] criada com sucesso!</info>");
			} catch (SchemaException $e) {
				$output->writeln('<error>Erro ao criar a tabela: ' . $e->getMessage() .'</error>');
			}
			

//######### Tabela anime_dublador #########################################################################################################################
			$tableName = 'anime_dublador';

			// Verifica se a tabela existe
			if ($schemaManager->tablesExist([$tableName])) {
				$schemaManager->dropTable($tableName);
				$output->writeln("<comment>Tabela [$tableName] apagada com sucesso!</comment>");
			}        

			$schema = new Schema();
			$table = $schema->createTable($tableName);			
			$table->addOption('charset', $params['charset']);
			$table->addOption('collate', $params['collation']);			
			$table->addColumn('anime_id', Types::SMALLINT, ['unsigned' => true]);
			$table->addColumn('dublador_id', Types::SMALLINT, ['unsigned' => true]);			
			$table->addForeignKeyConstraint('animes', ['anime_id'], ['id'], ['onDelete' => 'CASCADE']);
			$table->addForeignKeyConstraint('dubladores', ['dublador_id'], ['id'], ['onDelete' => 'CASCADE']);
			$table->addColumn('midia', Types::STRING, ['length' => 100, 'comment' => 'Trecho de áudio ou vídeo']);
			$table->addColumn('interprete', Types::STRING, ['length' => 50]);
			$table->addColumn('personagem', Types::STRING, ['length' => 50]);
			$table->addColumn('comentario', Types::TEXT, ['length' => 65535, 'notnull' => false]);

			$table->setPrimaryKey(['anime_id', 'dublador_id']);

			try {
				// Obtém a instrução SQL para criar a tabela
				$sql = $schema->toSql($conn->getDatabasePlatform());

				// Executa a instrução SQL
				foreach ($sql as $sqlStatement) {
					$conn->executeStatement($sqlStatement);
				}

				$output->writeln("<info>Tabela [$tableName] criada com sucesso!</info>");
			} catch (SchemaException $e) {
				$output->writeln('<error>Erro ao criar a tabela: ' . $e->getMessage() .'</error>');
			}			
			
			// Ativa as verificações de chave estrangeira
			$conn->executeStatement('SET FOREIGN_KEY_CHECKS=1');
		} else {
			$output->writeln('<comment>Nada foi feito.</comment>');
		}
	
        return Command::SUCCESS;
    }
}