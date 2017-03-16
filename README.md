# Valid Account
O Valid Account trata-se de um módulo de WHMCS baseado em API com código Open Source baseado na licença MIT nos quais estarão sendo colocado os créditos logo a baixo.
O Módulo tem de proposito integrar um sistema de verificação de CPF ou CNPJ como consulta pública a fim de verificar fraudes que podem haver em algum pedido feito no WHMCS, podendo assim validar de forma eficaz com a consulta.

# Requisitos necessários para instalação
- PHP 5.6 ou maior
- WHMCS 7.x ou superior
- PDO e Mysqli ativos

# Como instalar
Para começar você deve ter 3 campos para validação:
- CPF
- Data de Nascimento
- CNPJ <br/>
Caso utilize campo único para CPF e CNPJ poderá usa-lo também, lembrando que para a consulta de CPF é necessário ter a Data de Nascimento preenchido na conta de cada cliente, o módulo possui mascará para o campo CPF, CNPJ e Data De Nascimento e para campo único de CPF e CNPJ também. <br/>
Após ter criado os campos envie o valid-account para a pasta /modules/addons/ <br/>
E ative-o através do painel de módulos, ao entrar pela primeira vez, certifique de alterar as configurações colocando os campos correspondentes como CPF, CNPJ e Data de Nascimento, caso for campo único para CPF e CNPJ lembre-se de marcar o mesmo nas ambas seleções para CPF e CNPJ. <br/>
Lembre-se que caso não queira que o sistema de validação trave na hora do cadastro caso dados sejam inseridos errados basta desmarcar campo como obrigatório no momento que esteja criando os campos personalizados em seu whmcs.<br/>

# Créditos e Códigos
O Código de consulta e validação para CPF e CNPJ são baseados nos códigos fontes que você poderá conhecer cada código logo a baixo:<br/>
https://github.com/jansenfelipe/cpf-gratis <br/>
https://github.com/jansenfelipe/cnpj-gratis <br/>
https://github.com/digitalBush/jquery.maskedinput <br/>
https://github.com/tiagoporto/gerador-validador-cpf/ <br/>
http://www.geradorcnpj.com/javascript-validar-cnpj.htm <br/>

Módulo desenvolvido por Luciano Zanita, membro e fundador da http://WHMCS.RED/


# Considerações
Confira nosso site: http://whmcs.red/ <br/>
Confira nosso fórum: http://forum.whmcs.red/ <br/>
Use e indique aos seus amigos! :-)
