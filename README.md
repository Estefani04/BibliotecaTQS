# BibliotecaTQS

Sistema web de biblioteca (PHP + MySQL) usado no trabalho prático de **DevOps/QAOps** da disciplina de Qualidade de Software.
Usuários consultam o acervo, o histórico de retiradas e as multas; administradores cadastram livros, usuários e empréstimos.
A cada `push` ou pull request, um pipeline no GitHub Actions verifica o código com três quality gates.

## Estrutura

```
main/                 código do sistema (páginas PHP)
  admin/              telas do administrador
  usuario/            telas do usuário comum
  conexao.php         conexão com o banco (lê variáveis de ambiente)
src/                  regras de negócio testáveis (namespace App\)
  Multa.php           cálculo da multa por atraso (R$ 0,75/dia)
  MultaRepositorio.php  busca e grava multas no banco
  Pesquisa.php        pesquisa, ordenação e mensagens das listagens
tests/                testes unitários (PHPUnit)
  Integracao/         testes de integração com MySQL real
bibliotecaV2.sql      esquema do banco
.github/workflows/ci.yml  pipeline de CI
```

## Pré-requisitos

| Para…               | Precisa de                                                     |
|---------------------|----------------------------------------------------------------|
| Rodar o sistema     | PHP 8.0 ou superior com `mysqli` e `mbstring`, Apache, MySQL 8 |
| Rodar os testes     | PHP 8.2 ou superior e [Composer](https://getcomposer.org)      |

O XAMPP já traz Apache e PHP. O MariaDB que vem com ele (10.4) **não** reconhece a collation
`utf8mb4_0900_ai_ci` usada no `bibliotecaV2.sql`. Use MySQL 8 ou veja a observação no passo 2.

## Como rodar o sistema

### 1. Baixar o código

Clone o repositório dentro da pasta servida pelo Apache (no XAMPP, `C:\xampp\htdocs`):

```bash
cd C:/xampp/htdocs
git clone https://github.com/Estefani04/BibliotecaTQS.git
```

### 2. Criar o banco

Importe o esquema. Ele cria o banco `biblioteca` com as tabelas `usuarios`, `livros`, `emprestimos` e `multas`:

```bash
mysql -u root -p < bibliotecaV2.sql
```

Pelo phpMyAdmin: aba **Importar** → escolher `bibliotecaV2.sql` → **Executar**.

> **MariaDB (XAMPP):** se aparecer `Unknown collation: 'utf8mb4_0900_ai_ci'`, troque no arquivo
> `utf8mb4_0900_ai_ci` por `utf8mb4_general_ci` e importe de novo.

O esquema vem vazio. Crie o primeiro administrador. A senha inicial é `1234`, e o sistema pede para trocá-la no primeiro login:

```sql
INSERT INTO biblioteca.usuarios (nome, email, senha, tipo)
VALUES ('Administrador', 'admin@biblioteca.com', SHA2('1234', 256), 'admin');
```

### 3. Configurar a conexão

As credenciais **não ficam no código**. O arquivo `main/conexao.php` lê estas variáveis de ambiente:

| Variável  | Padrão (se não definida) |
|-----------|--------------------------|
| `DB_HOST` | `localhost`              |
| `DB_USER` | `root`                   |
| `DB_PASS` | *(vazio)*                |
| `DB_NAME` | `biblioteca`             |
| `DB_PORT` | `3306`                   |

O arquivo `.env.example` lista essas variáveis como referência. Copie para `.env` (que está no `.gitignore`) e preencha.

* **XAMPP padrão** (root sem senha): os valores padrão já funcionam e não precisa configurar nada.
* **Com senha:** defina as variáveis no Apache, por exemplo no fim do `httpd.conf`
  (XAMPP → Apache → Config), e reinicie o Apache:

  ```apache
  SetEnv DB_USER root
  SetEnv DB_PASS sua_senha_aqui
  ```

  Nunca coloque a senha em arquivos versionados.

### 4. Acessar

Com Apache e MySQL ligados, abra <http://localhost/BibliotecaTQS/main/login.php> e entre com o administrador criado no passo 2.

## Como rodar os testes e a análise estática

Instale as dependências de desenvolvimento (PHPUnit e PHPStan) uma vez:

```bash
composer install
```

| Comando                     | O que faz                                                        |
|-----------------------------|------------------------------------------------------------------|
| `composer test`             | testes unitários: multa, pesquisa, ordenação e mensagens         |
| `composer stan`             | análise estática com PHPStan (nível 5) em `src/` e `tests/`      |
| `composer test:integracao`  | testes de integração com MySQL (veja abaixo)                     |

### Testes de integração

Os testes em `tests/Integracao/` rodam contra um MySQL real e **apagam os dados das tabelas** a cada teste.
Por isso eles são ignorados (*skipped*), a menos que `TESTES_INTEGRACAO=1` esteja definido.
**Use um banco só para testes**, nunca o banco com dados reais.

```bash
mysql -u root -p -e "CREATE DATABASE biblioteca_teste"
sed 's/`biblioteca`/`biblioteca_teste`/g' bibliotecaV2.sql | mysql -u root -p
TESTES_INTEGRACAO=1 DB_NAME=biblioteca_teste DB_PASS=sua_senha composer test:integracao
```

No CI isso é automático: o pipeline sobe um container MySQL descartável, importa o `bibliotecaV2.sql` e roda os testes.

## Pipeline de CI (GitHub Actions)

Arquivo: `.github/workflows/ci.yml`. Roda a cada `push` e pull request:

1. Instala o PHP 8.3 e as dependências (`composer install`).
2. **Gate 1 – Lint:** `php -l` em todos os arquivos de `src/` e `tests/`. Pega erros de sintaxe.
3. **Gate 2 – PHPStan:** análise estática nível 5. Pega defeitos sem executar o código.
4. Sobe o MySQL de serviço e importa o `bibliotecaV2.sql`.
5. **Gate 3 – PHPUnit:** testes unitários e de integração.
6. Publica o relatório JUnit (`relatorio-testes`) como artefato, mesmo se algo falhar.

Se qualquer gate falha, o pipeline fica vermelho e os passos seguintes não rodam.

## Requisitos do sistema (enunciado)

<details>
<summary>Ver enunciado original</summary>

* **Menus de navegação:** menu principal, submenus e menus diferentes por perfil (administrador × usuário).
* **Formulários:** cadastro, edição e exclusão, validação de campos e feedback ao usuário.
* **Login com validação:** usuário e senha, validação no backend, senhas com hash, mensagens de erro e controle de acesso por nível.
* **Tela de gerenciamento (administrador):** CRUD de registros, gerenciamento de usuários, visão geral e permissões.
* **Tela de relatórios:** listagens, filtros, ordenação, tabelas e, opcionalmente, exportação para PDF/Excel.
* **Banco de dados:** MySQL, tabelas com chaves estrangeiras e comandos SELECT/INSERT/UPDATE/DELETE.
* **Sessões/cookies:** `$_SESSION` para manter o login, proteção de páginas restritas e logout.

</details>
