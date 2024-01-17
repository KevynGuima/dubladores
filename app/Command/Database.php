<?php
declare(strict_types=1);

namespace App\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Doctrine\DBAL\DriverManager;

class Database extends Command
{
    // Adicione uma propriedade para armazenar o contêiner
    private ContainerInterface $container;
    private $logger;

    // Injete o contêiner no construtor
    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
		$this->logger    = $container->get('logger');
    }

    protected function configure(): void
    {
        $this->setName('create:database')->setDescription('Cria um banco de dados com o nome que estiver em .env');
    }

	protected function execute(InputInterface $input, OutputInterface $output): int
    {
		$helper   = $this->getHelper('question');
		$question = new Question('<question>Deseja criar o banco de dados? (S/N):</question>');

		$question->setValidator(function ($answer) {
			if (!in_array(strtoupper($answer), ['S', 'N'])) {
				throw new \RuntimeException('Resposta inválida. Por favor, digite S ou N.');
			}

			return $answer;
		});

		$answer = $helper->ask($input, $output, $question);
		
		if (strtoupper($answer) === 'S') {
			$db     = $this->container->get('db');			
			$params = $db->getParams();
			$dbname = $params['dbname'];
			
			unset($params['dbname']);

			$conn = DriverManager::getConnection($params);

			try {
				//Informação: result retorna somente o numero de tabelas que foram apagadas junto com o banco se o banco nao tem nenhuma tabela retorna 0 e apaga somente o nome do banco do mysql
				$sql    = 'DROP DATABASE IF EXISTS ' . $dbname;
				$result = $conn->executeStatement($sql);
				if($result > 0) {
					$frase = 'Banco deletado com sucesso!';
					$output->writeln("<comment>$frase</comment>");
					$this->logger->info($frase);
				}
			} catch (\Exception $e) {
				$frase = 'Erro ao deletar banco de dados: ' . $e->getMessage();
				$this->logger->error($frase);
				$output->writeln("<error>$frase</error>");
			}

			try {
				// se o nome do banco viesse de um formulario para segurança usariamos = $conn->quoteIdentifier($databaseName)
				$sql    = "CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
				$result = $conn->executeStatement($sql);
				if($result > 0) {
					$frase = "Banco [$dbname] criado com sucesso!";
					$this->logger->info($frase);
					$output->writeln("<info>$frase</info>");
				}
			} catch (\Exception $e) {
				$output->writeln('<error>Erro ao criar o banco de dados: ' . $e->getMessage(). '</error>');
				
			}
		} else {
			$output->writeln('<comment>Nada foi feito.</comment>');
		}
	
        return Command::SUCCESS;
    }
}