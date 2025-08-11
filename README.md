# Gerenciador de Referências LIBRogr

Um sistema web leve para gerenciar, formatar e exportar referências bibliográficas. Construído com PHP puro e armazenamento em arquivos JSON, é uma solução portátil e fácil de instalar para pesquisadores, estudantes e acadêmicos.

_(Sugestão: Tire um screenshot da sua tela e substitua o link acima)_

## ✨ Funcionalidades

* **Gerenciamento Completo (CRUD):** Crie, visualize, edite e apague referências facilmente.

* **Sem Banco de Dados Relacional:** Utiliza arquivos JSON para armazenamento, eliminando a necessidade de MySQL, PostgreSQL, etc.

* **Formato de Dados Robusto:** Armazena os dados nativamente no formato **CSL-JSON**, garantindo compatibilidade e estrutura.

* **Templates de Formulário:** Interface com campos específicos para diferentes tipos de materiais (Artigos, Livros, Capítulos, Sites).

* **Importador de `.bib`:** Importe múltiplas referências de uma só vez a partir de arquivos BibTeX.

* **Formatação Dinâmica com CSL:** Visualize sua lista de referências em milhares de estilos de citação (ABNT, APA, Vancouver, etc.) simplesmente adicionando arquivos `.csl` da Zotero Style Repository.

* **Exportação Flexível:** Exporte referências individuais para os formatos **BibTeX**, **RIS** e **BibLaTeX**.

* **Busca e Paginação:** Encontre rapidamente o que precisa e navegue por toda a sua coleção.

* **Anotações Pessoais:** Adicione notas e resumos a cada referência para lembrar por que você a salvou.

## 🚀 Requisitos

Para executar este projeto, você precisará de:

* Um ambiente de servidor local (XAMPP, WAMP, MAMP, etc.).

* PHP `8.0` ou superior.

* A extensão PHP **`intl`** ativada.

* **Composer** para gerenciamento de dependências.

## ⚙️ Instalação

Siga estes passos para configurar o projeto em seu ambiente local:

1. **Clonar o Repositório** Abra o terminal e clone o projeto para o diretório do seu servidor (ex: `C:/xampp/htdocs/`):

   Bash

   ```
   git clone [URL_DO_SEU_REPOSITORIO] gerenciador
   ```

   Ou simplesmente baixe o ZIP e extraia a pasta `gerenciador` para o seu diretório web.

2. **Instalar Dependências** Navegue até a pasta do projeto no terminal e instale a biblioteca de citação com o Composer:

   Bash

   ```
   cd grlibrogr
   composer install
   ```

   Isso irá ler o arquivo `composer.json` e instalar a biblioteca `citeproc-php` na pasta `vendor/`.

3. **Ativar a Extensão `intl`**

   * Localize seu arquivo `php.ini` (no XAMPP, geralmente em `C:\xampp\php\php.ini`).

   * Abra o arquivo e procure pela linha `;extension=intl`.

   * Remova o ponto e vírgula (`;`) do início da linha para ativá-la. A linha deve ficar assim:

     Ini, TOML

     ```
     extension=intl
     ```

   * Salve o arquivo `php.ini`.

4. **Reiniciar Tudo**

   * **Reinicie o servidor Apache** através do painel de controle do XAMPP para que a alteração no `php.ini` tenha efeito.

   * **(Lembrete solicitado)** **Reinicie seu editor de código (como o VS Code)**. Isso garante que o terminal integrado e outras ferramentas reconheçam as novas dependências e configurações do ambiente.

5. **Permissões da Pasta** Dê permissão de escrita para o servidor na pasta `/data`. Em sistemas Windows com XAMPP, isso geralmente já está configurado. Em Linux/macOS, você talvez precise executar `chmod -R 775 data`.

6. **Configurar Estilos CSL**

   * Baixe os arquivos de estilo `.csl` que desejar da [Zotero Style Repository](https://www.zotero.org/styles). Certifique-se de baixar o arquivo "cru" (raw), não a página HTML.

   * Coloque os arquivos `.csl` baixados na pasta `/refmodels`.

   * Abra o arquivo `config.php` e edite a constante `DEFAULT_CSL_STYLE` para definir o seu estilo padrão (usando o nome do arquivo, ex: `'abnt-numero.csl'`).

7. **Acessar** Abra seu navegador e acesse o projeto (ex: `http://localhost/gerenciador` ou `http://localhost:8080/gerenciador`).

## 📋 Como Usar

* **Adicionar Referências:** Clique em "Inserir Nova Referência" e escolha o tipo de material. Preencha os campos e salve.

* **Importar:** Clique em "Importar Arquivo", selecione um arquivo `.bib` e as referências serão adicionadas à sua base.

* **Mudar Estilo de Visualização:** Use o seletor "Formato de Visualização" para aplicar dinamicamente qualquer um dos estilos CSL que você adicionou à pasta `/refmodels`.

* **Ver/Exportar:** Clique em "Ver" em qualquer referência para ir à página de detalhes. Lá você pode:

  * Visualizar a citação em outros estilos CSL.

  * Selecionar um formato de exportação (BibTeX, RIS, etc.) e usar o botão "Copiar".

  * Acessar a URL original da referência.

  * Ver suas notas e resumos pessoais.

* **Editar/Apagar:** Use os botões correspondentes na lista para gerenciar cada registro.

## 🏗️ Estrutura de Arquivos

```
/gerenciador/
|-- data/                 # Armazena os arquivos de dados
|   |-- references.json   # Base de dados principal (formato CSL-JSON)
|   |-- annotations.json  # Base de dados para notas e resumos
|-- refmodels/            # Coloque seus arquivos .csl aqui
|-- templates/            # Arquivos de template HTML/PHP
|-- vendor/               # Dependências do Composer (gerado automaticamente)
|-- Acoes.php             # Processa os formulários (salvar, apagar)
|-- AcoesImportar.php     # Processa a importação de arquivos .bib
|-- config.php            # Configurações principais e funções de ajuda
|-- csl_functions.php     # Funções de ajuda para CSL
|-- export_functions.php  # Funções para exportar para BibTeX, RIS, etc.
|-- index.php             # Arquivo principal e roteador
|-- style.css             # Folha de estilos principal
```

## 🔮 Funcionalidades Futuras

* \[ ] Suporte à importação de outros formatos (RIS, XML do EndNote).

* \[ ] Sistema de tags/etiquetas para as referências.

* \[ ] Geração de uma bibliografia completa (não apenas item por item) para exportação.

* \[ ] Suporte a múltiplos usuários (requer mudança para um sistema de banco de dados).

## 📄 Licença

Este projeto está licenciado sob a Licença MIT.
