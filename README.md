Utilizando o Alice em Projetos sem Doctrine
===========================================================

## Primeiros passos

Para utilizar o Alice com o Doctrine, mesmo em projetos onde não está sendo utilizado
o Doctrine, é necessário: 

- Criar um arquivo .yml com as definições da tabela (pasta config/yml/). Por exemplo:
	<pre lang="yaml"><code>
	# config/yml/Usuario.dcm.yml
	Usuario:
	  type: entity
	  table: usuario
	  id:
	    id:
	      type: integer
	      generator:
	        strategy: AUTO
	  fields:
	    name:
	      type: string
	    login:
	      type: string
	    email:
	      type: string
	    password:
	      type: string
  	</code></pre>

- Criar um arquivo .yml com os dados das tabelas (pasta fixtures/tables/). Por Exemplo:
	<pre lang="yaml"><code>
	# fixtures/tables/usuario.yml
	Usuario:
	  user1:
	    name: <firstName()> <lastName()>
	    login: <username()>
	    email: <email()>
	    password: pass1
    </code></pre>

- Gerar os arquivos de schema a partir dos arquivos .yml de definições da tabela. No terminal, digite:
	```$ php vendor/bin/doctrine orm:generate:entities config/php```

- Carregar os dados usando o script ./loader.php: ```php loader.php```

## Gerando arquivos Schema a partir do banco de dados

Em alguns projetos onde o Doctrine não está sendo utilizado será melhor iniciar copiando a 
estrutura de um banco de dados pronto, gerando os arquivos de schema e então dar início ao processo do item acima.

Para isso, rode no terminal:
	```$ php vendor/bin/doctrine.php orm:convert:mapping --from-database yaml config/yaml/```

Isso irá buscar todas as tabelas do banco (ver as configurações em bootstrap.php) e gerar os arquivos de schema na pasta config/yaml.