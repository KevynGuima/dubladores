<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="UserRepository")
 * @ORM\Table(name="usuarios")
 */
class User
{
    // Propriedades e métodos existentes

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $nome;

    /**
     * @ORM\Column(type="string")
     */
    private $email;
	
    /**
     * @ORM\Column(type="string")
     */
    private $senha;
	
  /**
     * @ORM\Column(name="data_nascimento", type="string")
     */
    private $dataNascimento;	
	
  /**
     * @ORM\Column(name="token_validacao", type="string")
     */
    private $tokenValidacao;
	
  /**
     * Define o nome do usuário.
     *
     * @param string $nome
     */
    public function setNome(string $nome)
    {
        $this->nome = $nome;
    }
	
  /**
     * Define a senha do usuário.
     *
     * @param string $senha
     */
    public function setSenha(string $senha)
    {
        $this->senha = password_hash($senha, PASSWORD_BCRYPT);
    }
	
  /**
     * Define o token do usuário.
     *
     */
    public function setTokenValidacao()
    {
        $this->tokenValidacao = bin2hex(random_bytes(32));
    }	

  /**
     * Define o email do usuário.
     *
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }
	
  /**
     * Define a data nascimento do usuário.
     *
     * @param string $dataNascimento
     */
    public function setDataNascimento(string $dataNascimento)
    {
        $this->dataNascimento = $dataNascimento;
    }	
	
    /**
     * Obtém a data de nascimento do usuário.
     *
     * @return string|null
     */
    public function getDataNascimento()
    {
        return $this->dataNascimento;
    }
	
    /**
     * Obtém o ID do usuário.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }
	
    /**
     * Obtém o nome do usuário.
     *
     * @return string|null
     */
    public function getNome()
    {
        return $this->nome;
    }
	
    /**
     * Obtém o nome do usuário.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }
	
 /**
     * Obtém o token de validação.
     *
     * @return string|null
     */
    public function getTokenValidacao()
    {
        return $this->tokenValidacao;
    }	
}