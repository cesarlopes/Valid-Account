<?php
//Carbon
use Carbon\Carbon;
//Laravel DataBase
use WHMCS\Database\Capsule;

//Chamando Composer Load
require_once 'vendor/autoload.php';
//Chamando Módulo de CPF e CNPJ
use JansenFelipe\CpfGratis\CpfGratis;
use JansenFelipe\CnpjGratis\CnpjGratis;

function valid_account_config() {
    $configarray = array(
    'name' => 'Valid Account',
    'description' => 'Sistema de validação de cadastro baseado em CPF/CNPJ e envio de documentação.',
    'version' => '1.0',
    'language' => 'portuguese-br',
    'author' => 'WHMCS.RED',
    );
    return $configarray;
}

function valid_account_activate($vars) {
    
    //Criando tabela de validação de documentos
	Capsule::schema()->create('mod_validaccount_documentos',
	    function ($table) {
	        /** @var \Illuminate\Database\Schema\Blueprint $table */
	        $table->increments('id');
	        $table->string('usuario');
	        $table->string('arquivo');
	        $table->string('tipo');
	        $table->string('status');
	        $table->string('data');
	        $table->string('data_aprovacao');
	        $table->string('motivo_status');
	    }
	);
    
    //Criando nova tabela
	Capsule::schema()->create('mod_validaccount',
	    function ($table) {
	        /** @var \Illuminate\Database\Schema\Blueprint $table */
	        $table->increments('id');
	        $table->string('cpf');
	        $table->string('data_nascimento');
	        $table->string('cnpj');
	        $table->string('tipoconta');
	        $table->string('idade');
	        $table->string('idademaxima');
	        $table->string('juridicocpf');
	        $table->string('documentacao');
	        $table->string('alerta_documentacao');
            $table->string('alerta_email');
            $table->string('template_documentacao_emanalise');
	        $table->string('template_documentacao_aprovado');
            $table->string('template_documentacao_reprovado');
	        $table->string('template_comprovante_emanalise');
            $table->string('template_comprovante_aprovado');
            $table->string('template_comprovante_reprovado');
            $table->string('privacidade');
            $table->string('attachments_pasta');
	    }
	);
	
	//Inserindo dados no banco de dados
    Capsule::connection()->transaction(
        function ($connectionManager)
        {
            /** @var \Illuminate\Database\Connection $connectionManager */
            $connectionManager->table('mod_validaccount')->insert(['cpf' => 'nulo','data_nascimento' => 'nulo','cnpj' => 'nulo','tipoconta' => 'nulo','idade' => '18','idademaxima' => '100','juridicocpf' => '1','documentacao' => '1','alerta_documentacao' => '1','template_documentacao_emanalise' => 'nulo','template_documentacao_aprovado' => 'nulo','template_documentacao_reprovado' => 'nulo','template_comprovante_emanalise' => 'nulo','template_comprovante_aprovado' => 'nulo','template_comprovante_reprovado' => 'nulo','alerta_email' => '1','privacidade' => '1','attachments_pasta' => '',]);
        }
    );
    
    //Retorno
    return array('status'=>'success','description'=>'Módulo Valid Account ativado com sucesso!');
    return array('status'=>'error','description'=>'Não foi possível ativar o módulo de Valid Account por causa de um erro desconhecido');
}
 
function valid_account_deactivate($vars) {
    //Remover Banco de Dados
	Capsule::schema()->drop('mod_validaccount');
	Capsule::schema()->drop('mod_validaccount_documentos');

    //Retorno
    return array('status'=>'success','description'=>'Módulo de Valid Account foi desativado com sucesso!');
    return array('status'=>'error','description'=>'Não foi possível desativar o módulo Valid Account por causa de um erro desconhecido');
}
function valid_account_clientarea($vars){
    global $attachments_dir;
        //Pegando URL do sistema no banco
    	foreach (Capsule::table('tblconfiguration')->WHERE('setting', 'SystemURL')->get() as $system){
	    	$urlsistema = $system->value;
		}
		//informacoes do modulo
			foreach (Capsule::table('mod_validaccount')->get() as $cvallid){
        	    $documentacao = $cvallid->documentacao;
        	    $attachments_pasta = $cvallid->attachments_pasta;
        	    
        	}
    $informacoes["statusdomod"] = $documentacao;
    $LANG = $vars['_lang'];
    $modulelink = $vars['modulelink'];
    $cliente_id = $_SESSION['uid'];
    $template = "valid_account";
    $informacoes["modulelink"] =  $modulelink;
    $informacoes["validaccount_lang"] = $vars['_lang'];
    $informacoes["urlsistema"] = $urlsistema;
    
    //verificação de status
        //verifica status do documento
        $totaldocumento = Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $cliente_id)->WHERE('tipo', '0')->count();
        //Compara se não tem resultados no mysql
        if($totaldocumento==0){
            //Caso não tiver documento aplica
            $informacoes['status_documento'] = "1";
            $informacoes['status_inputfiledocumento'] = "0";
        }
        //Caso tiver resultado prossegue
        else{
            //Consulta o status
            foreach(Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $cliente_id)->WHERE('tipo', '0')->get() as $documento){
                $status_doc = $documento->status;
                $motivo_status_doc = $documento->motivo_status;
            }
            //Repassa para o smarty
            $informacoes['status_documento'] = $status_doc;
            
            //Sistema de botão de envio
            if($status_doc=='4'){
                $informacoes['status_inputfiledocumento'] = "0";
                $informacoes['motivo_reprova_documento'] = $motivo_status_doc;
            }
            //caso não tiver sido enviado
            elseif($status_doc==1){
                $informacoes['status_inputfiledocumento'] = "0";
            }
            //Caso não for caso 4
            else{
                $informacoes['status_inputfiledocumento'] = "1";
            }
        }
        
        //verifica status da residencia
        $totalresidencia = Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $cliente_id)->WHERE('tipo', '1')->count();
        //Compara se não tem resultados no mysql
        if($totalresidencia==0){
            //Caso não tiver documento aplica
            $informacoes['status_residencia'] = "1";
            $informacoes['status_inputfileresidencia'] = "0";
        }
        //Caso tiver resultado prossegue
        else{
            //Consulta o status
            foreach(Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $cliente_id)->WHERE('tipo', '1')->get() as $comprovante){
                $status_residencia = $comprovante->status;
                $motivo_status_residencia = $comprovante->motivo_status;
            }
            //Repassa para o smarty
            $informacoes['status_residencia'] = $status_residencia;
            //Sistema de botão de envio
            if($status_residencia=='4'){
                $informacoes['status_inputfileresidencia'] = "0";
                $informacoes['motivo_reprova_comprovante'] = $motivo_status_residencia;
            }
            //caso não tiver sido enviado
            elseif($status_residencia==1){
                $informacoes['status_inputfileresidencia'] = "0";
            }
            //Caso não for caso 4
            else{
                $informacoes['status_inputfileresidencia'] = "1";
            }
        }
    
    //Verifica e faz upload
    if($_POST['postenviovalidaaccount'] == "true" && $documentacao=="1"){
        //Verifica se foi enviado os arquivos
        if(!empty($_FILES['documento']['name']) or !empty($_FILES['comprovante']['name'])){
            //verifica se é submit dos dois arquivos juntos
            if(!empty($_FILES['documento']['name']) && !empty($_FILES['comprovante']['name'])){
                $informacoes = header("Location: ".$urlsistema."index.php?m=valid_account&va=indisponivel");
            }
            //caso não for vai tentar ver qual foi enviado e processar ele
            else{
                //verifica se é só o documento
                if(!empty($_FILES['documento']['name'])){
                    //Data atual
                    $data = date('d/m/Y H:i:s');
                    // Pasta onde o arquivo vai ser salvo
            		$pasta = ''.$attachments_dir.'/'.$attachments_pasta.'';
            		//Tamanho máximo de arquivo
            		$tamanho_max = 1024 * 1024 * 5;
            		//Extensões permitidas
            		$extensoes = array('jpeg', 'jpg', 'png', 'gif', 'pdf');
            		//Deseja renomear?
            		$renomear = true;
            		//Verifica a extensão
            		$extensao = strtolower(end(explode('.', $_FILES['documento']['name'])));
            		if(array_search($extensao, $extensoes) === false){
            			//Redireciona caso a extensão não seja permitida
            		  	$informacoes = header("Location: ".$urlsistema."index.php?m=valid_account&va=extensao");
            		}
            		//Verifica tamanho máximo
            		if($tamanho_max < $_FILES['documento']['size']){
            		  	$informacoes = header("Location: ".$urlsistema."index.php?m=valid_account&va=tamanho");
            		}
            		//Verifica se quer renomear
            		if($renomear == true){
            		  	$nome_final_documento = md5(time()).'.'.$extensao.'';
            		}
            		else{
            		  	// Mantém o nome original do arquivo
            		  	$nome_final_documento = $_FILES['documento']['name'];
            		}
            		if (move_uploaded_file($_FILES['documento']['tmp_name'], $pasta . $nome_final_documento)) {
            		  	$status_envio = TRUE;
            		}
            		else{
            			$status_envio = FALSE;
            		}
            		//link final da mensagem
            		$linkcompletodoc = "".$nome_final_documento."";
            		
            		//Verifica se é um novo envio ou substituição em caso de reprovado
            		$totalregistros = Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $cliente_id)->WHERE('tipo', '0')->count();
            		
            		//Verifica se a imagem foi enviada
            		if($status_envio=="TRUE"){
                		//Cria a função para ser novo ou substituição
                		if($totalregistros==0){
                		    //Classe PDO
                		    $pdo = Capsule::connection()->getPdo();
                            $pdo->beginTransaction();
                            //Função de inserir no bd
                            try{
                                $statement = $pdo->prepare('insert into mod_validaccount_documentos (usuario, arquivo, tipo, status, data, data_aprovacao, motivo_status) values (:usuario, :arquivo, :tipo, :status, :data, :data_aprovacao, :motivo_status)');
                                $statement->execute(
                                    [
                                        ':usuario' => $cliente_id,
                                        ':arquivo' => $linkcompletodoc,
                                        ':tipo' => '0',
                                        ':status' => '2',
                                        ':data' => $data,
                                        ':data_aprovacao' => '',
                                        ':motivo_status' => '',
                                    ]
                                );
                                $pdo->commit();
                                //bd valid
                        		foreach (Capsule::table('mod_validaccount')->get() as $cvallid){
                                    $alerta_email = $cvallid->alerta_email;
                            	    $template_documentacao_emanalise = $cvallid->template_documentacao_emanalise;
                                }
                                if($alerta_email==1){
                                    $id_user = $_SESSION['uid'];
                                    $admconsulta = Capsule::table('tbladmins')->WHERE('roleid', '1')->limit(1)->get();
                                    $administrador = $admconsulta->username;
                                    $valores["id"] = $id_user;
                                    //Email a ser enviado
                                    $valores["messagename"] = $template_documentacao_emanalise;
                                    //Comando a ser executado na função
                                    $comando = "sendemail";
                                    //executa comando
                                    $executar = localAPI($comando, $valores, $administrador);
                                }
                                //Inserindo dados no banco de dados TODOLIST
                                Capsule::connection()->transaction(
                                    function ($connectionManager)
                                    {
                                        /** @var \Illuminate\Database\Connection $connectionManager */
                                        $connectionManager->table('tbltodolist')->insert(['date' => ''.date('Y-m-d').'','title' => 'Valid Account - Documentação para validação','description' => 'O cliente ID #'.$_SESSION["uid"].' esta aguardando validação de documentação, por favor verificar no perfil do cliente.','admin' => '0','status' => 'Pending','duedate' => ''.date('Y-m-d', strtotime('+3 days')).'',]);
                                    }
                                );
                                
                                //mensagem de sucesso
                                $informacoes = header("Location: ".$urlsistema."index.php?m=valid_account&va=enviado");
                            }
                            //Caso tiver ter dado problemas
                            catch (\Exception $e) {
                                $informacoes = header("Location: ".$urlsistema."index.php?m=valid_account&va=erro");
                                //$pdo->rollBack();
                            }
                		}
                		else{
                		    //Captura de ID do ja cadastrado
                		    foreach(Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $cliente_id)->WHERE('tipo', '0')->get() as $docidb){
                		        $id_doc_db = $docidb->id;
                		    }
                		    //Cria função de update
                		    try {
                		        //update na tabela
                                $updatedoc = Capsule::table('mod_validaccount_documentos')->WHERE('id', $id_doc_db)->WHERE('usuario', $cliente_id)->WHERE('tipo', '0')->update(['arquivo' => $linkcompletodoc, 'status' => '2',]);
                                //bd valid
                        		foreach (Capsule::table('mod_validaccount')->get() as $cvallid){
                                    $alerta_email = $cvallid->alerta_email;
                            	    $template_documentacao_emanalise = $cvallid->template_documentacao_emanalise;
                                }
                                if($alerta_email==1){
                                    $id_user = $_SESSION['uid'];
                                    $admconsulta = Capsule::table('tbladmins')->WHERE('roleid', '1')->limit(1)->get();
                                    $administrador = $admconsulta->username;
                                    $valores["id"] = $id_user;
                                    //Email a ser enviado
                                    $valores["messagename"] = $template_documentacao_emanalise;
                                    //Comando a ser executado na função
                                    $comando = "sendemail";
                                    //executa comando
                                    $executar = localAPI($comando, $valores, $administrador);
                                }
                                //Inserindo dados no banco de dados TODOLIST
                                Capsule::connection()->transaction(
                                    function ($connectionManager)
                                    {
                                        /** @var \Illuminate\Database\Connection $connectionManager */
                                        $connectionManager->table('tbltodolist')->insert(['date' => ''.date('Y-m-d').'','title' => 'Valid Account - Documentação para validação','description' => 'O cliente ID #'.$_SESSION["uid"].' esta aguardando validação de documentação, por favor verificar no perfil do cliente.','admin' => '0','status' => 'Pending','duedate' => ''.date('Y-m-d', strtotime('+3 days')).'',]);
                                    }
                                );
                                
                                //mensagem de sucesso
                                $informacoes = header("Location: ".$urlsistema."index.php?m=valid_account&va=enviado");
                                
                            }
                            catch (\Exception $e){
                                //mensagem de erro
                                $informacoes = header("Location: ".$urlsistema."index.php?m=valid_account&va=erro");
                            }
                		}
            		}
            		//Caso não tiver sido enviada o documento
            		else{
            		    $informacoes = header("Location: ".$urlsistema."index.php?m=valid_account&va=erro");
            		}
 
                    
                }
                //verifica se é só o comprovante
                if(!empty($_FILES['comprovante']['name'])){
                    //Data atual
                    $data = date('d/m/Y H:i:s');
                    // Pasta onde o arquivo vai ser salvo
            		$pasta = ''.$attachments_dir.'/'.$attachments_pasta.'';
            		//Tamanho máximo de arquivo
            		$tamanho_max = 1024 * 1024 * 5;
            		//Extensões permitidas
            		$extensoes = array('jpeg', 'jpg', 'png', 'gif', 'pdf');
            		//Deseja renomear?
            		$renomear = true;
            		//Verifica a extensão
            		$extensao = strtolower(end(explode('.', $_FILES['comprovante']['name'])));
            		if(array_search($extensao, $extensoes) === false){
            			//Redireciona caso a extensão não seja permitida
            		  	$informacoes = header("Location: ".$urlsistema."index.php?m=valid_account&va=extensao");
            		}
            		//Verifica tamanho máximo
            		if($tamanho_max < $_FILES['comprovante']['size']){
            		  	$informacoes = header("Location: ".$urlsistema."index.php?m=valid_account&va=tamanho");
            		}
            		//Verifica se quer renomear
            		if($renomear == true){
            		  	$nome_final_comprovante = md5(time()).'.'.$extensao.'';
            		}
            		else{
            		  	// Mantém o nome original do arquivo
            		  	$nome_final_comprovante = $_FILES['comprovante']['name'];
            		}
            		if (move_uploaded_file($_FILES['comprovante']['tmp_name'], $pasta . $nome_final_comprovante)) {
            		  	$status_envio = TRUE;
            		}
            		else{
            			$status_envio = FALSE;
            		}
            		//link final da mensagem
            		$linkcompletodoc = "".$nome_final_comprovante."";
            		
            		//Verifica se é um novo envio ou substituição em caso de reprovado
            		$totalregistros = Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $cliente_id)->WHERE('tipo', '1')->count();
            		
            		//Verifica se a imagem foi enviada
            		if($status_envio=="TRUE"){
                		//Cria a função para ser novo ou substituição
                		if($totalregistros==0){
                		    //Classe PDO
                		    $pdo = Capsule::connection()->getPdo();
                            $pdo->beginTransaction();
                            //Função de inserir no bd
                            try{
                                $statement = $pdo->prepare('insert into mod_validaccount_documentos (usuario, arquivo, tipo, status, data, data_aprovacao, motivo_status) values (:usuario, :arquivo, :tipo, :status, :data, :data_aprovacao, :motivo_status)');
                                $statement->execute(
                                    [
                                        ':usuario' => $cliente_id,
                                        ':arquivo' => $linkcompletodoc,
                                        ':tipo' => '1',
                                        ':status' => '2',
                                        ':data' => $data,
                                        ':data_aprovacao' => '',
                                        ':motivo_status' => '',
                                    ]
                                );
                                $pdo->commit();
                                //bd valid
                        		foreach (Capsule::table('mod_validaccount')->get() as $cvallid){
                                    $alerta_email = $cvallid->alerta_email;
                            	    $template_comprovante_emanalise = $cvallid->template_comprovante_emanalise;
                                }
                                if($alerta_email==1){
                                    $id_user = $_SESSION['uid'];
                                    $admconsulta = Capsule::table('tbladmins')->WHERE('roleid', '1')->limit(1)->get();
                                    $administrador = $admconsulta->username;
                                    $valores["id"] = $id_user;
                                    //Email a ser enviado
                                    $valores["messagename"] = $template_comprovante_emanalise;
                                    //Comando a ser executado na função
                                    $comando = "sendemail";
                                    //executa comando
                                    $executar = localAPI($comando, $valores, $administrador);
                                }
                                //Inserindo dados no banco de dados TODOLIST
                                Capsule::connection()->transaction(
                                    function ($connectionManager)
                                    {
                                        /** @var \Illuminate\Database\Connection $connectionManager */
                                        $connectionManager->table('tbltodolist')->insert(['date' => ''.date('Y-m-d').'','title' => 'Valid Account - Documentação para validação','description' => 'O cliente ID #'.$_SESSION["uid"].' esta aguardando validação de documentação, por favor verificar no perfil do cliente.','admin' => '0','status' => 'Pending','duedate' => ''.date('Y-m-d', strtotime('+3 days')).'',]);
                                    }
                                );
                                
                                //mensagem de sucesso
                                $informacoes = header("Location: ".$urlsistema."index.php?m=valid_account&va=enviado");
                                
                            }
                            //Caso tiver ter dado problemas
                            catch (\Exception $e) {
                                $informacoes = header("Location: ".$urlsistema."index.php?m=valid_account&va=erro");
                                //$pdo->rollBack();
                            }
                		}
                		else{
                		    //Captura de ID do ja cadastrado
                		    foreach(Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $cliente_id)->WHERE('tipo', '1')->get() as $docidb){
                		        $id_doc_db = $docidb->id;
                		    }
                		    //Cria função de update
                		    try {
                		        //update na tabela
                                $updatedoc = Capsule::table('mod_validaccount_documentos')->WHERE('id', $id_doc_db)->WHERE('usuario', $cliente_id)->WHERE('tipo', '1')->update(['arquivo' => $linkcompletodoc, 'status' => '2',]);
                                //bd valid
                        		foreach (Capsule::table('mod_validaccount')->get() as $cvallid){
                                    $alerta_email = $cvallid->alerta_email;
                            	    $template_documentacao_emanalise = $cvallid->template_documentacao_emanalise;
                                }
                                if($alerta_email==1){
                                    $id_user = $_SESSION['uid'];
                                    $admconsulta = Capsule::table('tbladmins')->WHERE('roleid', '1')->limit(1)->get();
                                    $administrador = $admconsulta->username;
                                    $valores["id"] = $id_user;
                                    //Email a ser enviado
                                    $valores["messagename"] = $template_documentacao_emanalise;
                                    //Comando a ser executado na função
                                    $comando = "sendemail";
                                    //executa comando
                                    $executar = localAPI($comando, $valores, $administrador);
                                }
                                //Inserindo dados no banco de dados TODOLIST
                                Capsule::connection()->transaction(
                                    function ($connectionManager)
                                    {
                                        /** @var \Illuminate\Database\Connection $connectionManager */
                                        $connectionManager->table('tbltodolist')->insert(['date' => ''.date('Y-m-d').'','title' => 'Valid Account - Documentação para validação','description' => 'O cliente ID #'.$_SESSION["uid"].' esta aguardando validação de documentação, por favor verificar no perfil do cliente.','admin' => '0','status' => 'Pending','duedate' => ''.date('Y-m-d', strtotime('+3 days')).'',]);
                                    }
                                );
                                
                                //mensagem de sucesso
                                $informacoes = header("Location: ".$urlsistema."index.php?m=valid_account&va=enviado");
                            }
                            catch (\Exception $e){
                                //mensagem de erro
                                $informacoes = header("Location: ".$urlsistema."index.php?m=valid_account&va=erro");
                            }
                		}
            		}
            		//Caso não tiver sido enviada o documento
            		else{
            		    $informacoes = header("Location: ".$urlsistema."index.php?m=valid_account&va=erro");
            		}
                }
            }
        }
        //Redireciona caso não houver nenhum submit de arquivo
        else{
            $informacoes = header("Location: ".$urlsistema."index.php?m=valid_account&va=nulo");
        }
    }
    
    return array(
        'pagetitle' => 'Valid Account',
        'breadcrumb' => array($modulelink=>'valid_account'),
        'templatefile' => $template,
        'requirelogin' => true,
        'vars' => $informacoes,
    );
    
}


function valid_account_output($vars){
    global $attachments_dir;
//Replace de Textos
function replacetexto($string) {
    $string = preg_replace('/[áàãâä]/ui', 'a', $string);
    $string = preg_replace('/[éèêë]/ui', 'e', $string);
    $string = preg_replace('/[íìîï]/ui', 'i', $string);
    $string = preg_replace('/[óòõôö]/ui', 'o', $string);
    $string = preg_replace('/[úùûü]/ui', 'u', $string);
    $string = preg_replace('/[ç]/ui', 'c', $string);
    $string = preg_replace('/[^a-z0-9]/i', '_', $string);
    $string = preg_replace('/_+/', '', $string);
    return $string;
}
//Chamando Parametros
$paramscpf = CpfGratis::getParams();
$paramscnpj = CnpjGratis::getParams();

    //Linguagem
    $LANG = $vars['_lang'];

    	//URL Do Sistema
    	//Pegando URL do sistema no banco
    	foreach (Capsule::table('tblconfiguration')->WHERE('setting', 'SystemURL')->get() as $system){
	    	$urlsistema = $system->value;
		}

    //Salvando informações de configuração
	if($_GET['config']=='salvar'){
		try{
			$updatedUserCount = Capsule::table('mod_validaccount')->update(['cpf' => $_POST['cpf'],'data_nascimento' => $_POST['data-nascimento'],'cnpj' => $_POST['cnpj'],'tipoconta' => $_POST['tipoconta'],'juridicocpf' => $_POST['juridicocpf'],'idade' => $_POST['idade'],'idademaxima' => $_POST['idademaxima'],'documentacao' => $_POST['documentacao'],'alerta_documentacao' => $_POST['alerta_documentacao'],'template_documentacao_emanalise' => $_POST['template_documentacao_emanalise'],'template_documentacao_aprovado' => $_POST['template_documentacao_aprovado'],'template_documentacao_reprovado' => $_POST['template_documentacao_reprovado'],'template_comprovante_emanalise' => $_POST['template_comprovante_emanalise'],'template_comprovante_aprovado' => $_POST['template_comprovante_aprovado'],'template_comprovante_reprovado' => $_POST['template_comprovante_reprovado'],'alerta_email' => $_POST['alerta_email'],'privacidade' => $_POST['privacidade'],'attachments_pasta' => $_POST['attachments_pasta'],]);
		    //Sucesso em salvar
		    echo '<div class="alert alert-success">'.$LANG["alertasalvar"].'</div>';
		}
		//Caso não conseguir, exibirá o erro
		catch (\Exception $e){
			echo '<div class="alert alert-danger">'.$LANG["alertasalvarerro"].'</div>';
		}
	}

    //Pegando informações da tabela do módulo.
	/** @var stdClass $cvallid */
	foreach (Capsule::table('mod_validaccount')->get() as $cvallid){
	    $cpfcampo = $cvallid->cpf;
	    $nascimentocampo = $cvallid->data_nascimento;
	    $cnpjcampo = $cvallid->cnpj;
	    $tipoconta = $cvallid->tipoconta;
	    $idadesistema = $cvallid->idade;
	    $idadesistemamaxima = $cvallid->idademaxima;
	    $juridicocpf = $cvallid->juridicocpf;
	    $documentacao = $cvallid->documentacao;
	    $alerta_documentacao = $cvallid->alerta_documentacao;
        $alerta_email = $cvallid->alerta_email;
	    $template_documentacao_emanalise = $cvallid->template_documentacao_emanalise;
        $template_documentacao_aprovado = $cvallid->template_documentacao_aprovado;
        $template_documentacao_reprovado = $cvallid->template_documentacao_reprovado;
	    $template_comprovante_emanalise = $cvallid->template_comprovante_emanalise;
        $template_comprovante_aprovado = $cvallid->template_comprovante_aprovado;
        $template_comprovante_reprovado = $cvallid->template_comprovante_reprovado;
        $privacidade = $cvallid->privacidade;
        $attachments_pasta = $cvallid->attachments_pasta;
	}

    //Consultar
    if($_GET['acao']=='consultar'){
    	//Verifica se foi enviado algum post
    	if(!empty($_POST['captcha'])){
	    	//Verifica se é CPF
	    	if($_POST['tipo']=='cpf'){
	    		//Verifica se é uma conta existente
	    		if($_POST['formabusca']=='existente'){
	    			//Verificando campos foram preenchidos corretamente
	    			if(!empty($_POST['captcha']) && !empty($_POST['cookiecpf']) && !empty($_POST['usuarioscadastrados'])){
		    			//Recebe o Post
		    			$cookie = $_POST['cookiecpf'];
		    			$usuario = $_POST['usuarioscadastrados'];
		    			$recebecaptcha = $_POST['captcha'];
		    			$token = $_POST['token'];

		    			//Separando Dados
		    			$variaveldousuario = explode("|",$usuario);
		    			//Tratamento dos Campos
		    			$cpfcorreto = replacetexto($variaveldousuario[1]);
		    			$nascimentocorreto = replacetexto($variaveldousuario[2]);
		    			//Verifica se o campo não é vazio
		    			if($cpfcorreto==""){
		    				header('Location: addonmodules.php?module=valid_account&erro=7');
		    			}
		    			//Caso não for prossegue
		    			else{
		    				//Encaminhar Dados
		    				$verificarcpf = CpfGratis::consulta($cpfcorreto, $nascimentocorreto, $recebecaptcha, $cookie);
	    				}
	    			}
	    			else{
						header('Location: addonmodules.php?module=valid_account&erro=1');
					}
	    		}
	    		//verifica se a consulta é avulsa
	    		if($_POST['formabusca']=='avulso'){
	    			//Verificando campos foram preenchidos corretamente
	    			if(!empty($_POST['captcha']) && !empty($_POST['cookiecpf']) && !empty($_POST['cpf-avulso']) && !empty($_POST['nascimento-avulso'])){

	    					//Recebe o Post
			    			$cookie = $_POST['cookiecpf'];
			    			$cpfavulso = $_POST['cpf-avulso'];
			    			$nascimentoavulso = $_POST['nascimento-avulso'];
			    			$recebecaptcha = $_POST['captcha'];
			    			$token = $_POST['token'];

	    				//Tratamento dos Campos
		    			$cpfcorreto = replacetexto($cpfavulso);
		    			$nascimentocorreto = replacetexto($nascimentoavulso);
	    				//Encaminhar Dados
	    				$verificarcpf = CpfGratis::consulta($cpfcorreto, $nascimentocorreto, $recebecaptcha, $cookie);
					}
					//Redireciona caso algun dos campos vier em branco
					else{
						header('Location: addonmodules.php?module=valid_account&erro=2');
					}

	    		}
	    	}
	    	//Verifica se é CNPJ
	    	if($_POST['tipo']=='cnpj'){
	    		//Verifica se é uma conta existente
	    		if($_POST['formabusca']=='existente'){
	    			//Verificando campos foram preenchidos corretamente
	    			if(!empty($_POST['captcha']) && !empty($_POST['cookiecnpj']) && !empty($_POST['usuarioscadastrados'])){

		    			//Recebe o Post
		    			$cookie = $_POST['cookiecnpj'];
		    			$usuario = $_POST['usuarioscadastrados'];
		    			$recebecaptcha = $_POST['captcha'];
		    			$token = $_POST['token'];

		    			//Separando Dados
		    			$variaveldousuario = explode("|",$usuario);
			    			//Tratamento dos Campos
			    				$cnpj = replacetexto($variaveldousuario[1]);
		    			
		    			//Verificando se não é campo vazio
		    			if($cnpj==""){
		    				//redireciona caso for vazio
		    				header('Location: addonmodules.php?module=valid_account&erro=6');
	    				}
	    				//Se não for prossegue a consulta
	    				else{
	    					//Encaminhar Dados
		    				$verificarcnpj = CnpjGratis::consulta($cnpj, $recebecaptcha, $cookie);
	    				}
	    			}
	    			else{
						header('Location: addonmodules.php?module=valid_account&erro=4');
					}
	    		}
	    		//verifica se a consulta é avulsa
	    		if($_POST['formabusca']=='avulso'){
	    			//Verificando campos foram preenchidos corretamente
	    			if(!empty($_POST['captcha']) && !empty($_POST['cookiecnpj']) && !empty($_POST['cnpj-avulso'])){

	    					//Recebe o Post
			    			$cookie = $_POST['cookiecnpj'];
			    			$cnpjavulso = $_POST['cnpj-avulso'];
			    			$recebecaptcha = $_POST['captcha'];
			    			$token = $_POST['token'];

	    				//Tratamento dos Campos
		    			$cnpjcorreto = replacetexto($cnpjavulso);
	    				//Encaminhar Dados
	    				$verificarcnpj = CnpjGratis::consulta($cnpjcorreto, $recebecaptcha, $cookie);
					}
					//Redireciona caso algun dos campos vier em branco
					else{
						header('Location: addonmodules.php?module=valid_account&erro=5');
					}
	    		}
	    	}
	    }
	    //Redireciona caso foi uma entrada sem POST
		else{
			header('Location: addonmodules.php?module=valid_account&erro=3');
		}
    }

	//Mensagens de Erro
	if($_GET['erro']=='1'){
		echo '<div class="alert alert-danger">'.$LANG["alerta1"].'</div>';
	}
	if($_GET['erro']=='2'){
		echo '<div class="alert alert-danger">'.$LANG["alerta2"].'</div>';
	}
	if($_GET['erro']=='3'){
		echo '<div class="alert alert-danger">'.$LANG["alerta3"].'</div>';
	}
	if($_GET['erro']=='4'){
		echo '<div class="alert alert-danger">'.$LANG["alerta4"].'</div>';
	}
	if($_GET['erro']=='5'){
		echo '<div class="alert alert-danger">'.$LANG["alerta5"].'</div>';
	}
	if($_GET['erro']=='6'){
		echo '<div class="alert alert-danger">'.$LANG["alerta6"].'</div>';
	}
	if($_GET['erro']=='7'){
		echo '<div class="alert alert-danger">'.$LANG["alerta7"].'</div>';
	}
	if($_GET['acao']=='consultar'){
		echo '<div class="alert alert-success">'.$LANG['alertaconsultar'].'</div>';
	}
?>
<div class="panel panel-default">
  <div class="panel-heading"><i class="fa fa-shield" aria-hidden="true"></i> <B>Valid Account</B> - <?=$LANG["consulta-receita"];?></div>
  <div class="panel-body">
   
    <div class="row">
    	<form action="addonmodules.php?module=valid_account&acao=consultar" method="POST">
    	<input type="hidden" name="cookiecpf" value="<?=$paramscpf['cookie'];?>" />
    	<input type="hidden" name="cookiecnpj" value="<?=$paramscnpj['cookie'];?>" />
        <!--Bloco 1-->
        <div class="col-md-8">
            <!--Tipo de conta-->
            <div class="panel panel-default">
              <div class="panel-heading"><i class="fa fa-user" aria-hidden="true"></i> <?=$LANG["tipoconta"];?></div>
              <div class="panel-body">
                <select name="tipo" id="tipo" class="form-control" required="">
                    <option value="cpf"><?=$LANG["pf"];?></option>
                    <option value="cnpj"><?=$LANG["pj"];?></option>
                </select>
              </div>
            </div>
            <!--Forma de Busca-->
            <div class="panel panel-default">
              <div class="panel-heading"><i class="fa fa-search" aria-hidden="true"></i> <?=$LANG["formabusca"];?></div>
              <div class="panel-body">
                <select name="formabusca" id="formabusca" class="form-control" required="">
                    <option value="existente"><?=$LANG["exitente"];?></option>
                    <option value="avulso"><?=$LANG["avulso"];?></option>
                </select>
              </div>
            </div>
            <!--Selecionar Conta de usuário-->
            <div class="panel panel-default" id="usuarios">
              <div class="panel-heading"><i class="fa fa-users" aria-hidden="true"></i> <?=$LANG["usuariosexistente"];?></div>
              <div class="panel-body">
                <select name="usuarioscadastrados" id="usuarioscadastrados" class="form-control">
                	<?php
                		$pdo = Capsule::connection()->getPdo();
						$pdo->beginTransaction();
						$consulta_pf = $pdo->prepare("SELECT `a`.`id`,`a`.`firstname`, `a`.`lastname`, `b`.`value` as `cpf`, `c`.`value` as `nascimento` FROM `tblclients` as `a` JOIN `tblcustomfieldsvalues` as `b` ON `b`.`relid` = `a`.`id` JOIN `tblcustomfieldsvalues` as `c` ON `c`.`relid` = `a`.`id` WHERE `b`.`fieldid` = :campocpf AND `c`.`fieldid` = :camponascimento order by `a`.`firstname`");
                		$consulta_pf->execute(
					        [
					            ':campocpf' => $cpfcampo,
					            ':camponascimento' => $nascimentocampo,
					            
					        ]
					    );
					    $pdo->commit();

					     foreach ($consulta_pf as $row) {
					     	$idusuario = $row['id'] . PHP_EOL;
					     	$primeironome = $row['firstname'] . PHP_EOL;
					     	$segundonome = $row['lastname'] . PHP_EOL;
					        $cpfdados = $row['cpf'] . PHP_EOL;
					        $nascimentodados = $row['nascimento'] . PHP_EOL;
					        $option_pf .= '<option value="'.$idusuario.'|'.$cpfdados.'|'.$nascimentodados.'">'.$primeironome.' '.$segundonome.'</option>';
					    }

					    $pdo->beginTransaction();
						$consulta_pj = $pdo->prepare("SELECT `a`.`id`,`a`.`firstname`, `a`.`lastname`, `a`.`companyname`, `b`.`value` as `cnpj` FROM `tblclients` as `a` JOIN `tblcustomfieldsvalues` as `b` ON `b`.`relid` = `a`.`id` WHERE `b`.`fieldid` = :campocnpj order by `a`.`firstname`");
                		$consulta_pj->execute(
					        [
					            ':campocnpj' => $cnpjcampo,
					            
					        ]
					    );
					    $pdo->commit();

					     foreach ($consulta_pj as $row2) {
					     	$idusuario = $row2['id'] . PHP_EOL;
					     	$primeironome = $row2['firstname'] . PHP_EOL;
					     	$segundonome = $row2['lastname'] . PHP_EOL;
					     	$nomeempresa = $row2['companyname'] . PHP_EOL;
					        $cnpjdados = $row2['cnpj'] . PHP_EOL;
					            if(!empty(trim($cnpjdados))){
					                $option_pj .= '<option value="'.$idusuario.'|'.$cnpjdados.'">'.$nomeempresa.' ('.$cnpjdados.')</option>';
					            }
					    }
                	?>
                </select>
              </div>
            </div>
            <!--Pesquisa Avulsa-->
            <div class="panel panel-default" id="avulsopesquisa">
              <div class="panel-heading"><i class="fa fa-users" aria-hidden="true"></i> <?=$LANG["pesavulsa"];?></div>
              <div class="panel-body">
              	<!--Fisico-->
              	<div id="avulsofisico">
	                <div class="row">
					  <div class="col-md-6">
					  	<div class="form-group">
	                        <label><?=$LANG["cpf"];?></label>
	                        <input name="cpf-avulso" type="text" class="form-control" id="cpf-avulso" />
	                    </div>
					  </div>
					  <div class="col-md-6">
					  	<div class="form-group">
	                        <label><?=$LANG["nascimento"];?></label>
	                        <input name="nascimento-avulso" type="text" class="form-control" id="nascimento-avulso" />
	                    </div>
					  </div>
					</div>
				</div>
				<!--Juridico-->
				<div id="avulsojuridico">
					<div class="form-group">
                        <label><?=$LANG["cnpj"];?></label>
                        <input name="cnpj-avulso" type="text" class="form-control" id="cnpj-avulso" />
                    </div>
				</div>
              </div>
            </div>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#pesquisar"><i class="fa fa-search-plus" aria-hidden="true"></i> <?=$LANG["verificardados"];?></button>

        	<!--Captcha-->
        	<div id="pesquisar" class="modal fade" role="dialog">
			  <div class="modal-dialog">
			    <!-- Modal content-->
			    <div class="modal-content">
			      <div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal">&times;</button>
			        <h4 class="modal-title"><?=$LANG["humano"];?></h4>
			      </div>
			      <div class="modal-body">
			        <div class="row">
			          <div class="col-md-4">
			          	 <i class=thumbnail style="margin-top: 10px;"><img id="captchaimg" src="<?=$paramscpf['captchaBase64'];?>" /></i>
			          </div>
					  <div class="col-md-8">
					  	<div class="form-group">
	                        <label><?=$LANG["captchadigite"];?></label>
	                        <input name="captcha" type="text" class="form-control" id="captcha" placeholder="<?=$LANG["captchaplaceholder"];?>" />
	                    </div>
					  </div>
					</div>
			      </div>
			      <div class="modal-footer">
			      	<input type="submit" class="btn btn-success" value="<?=$LANG["verificar"];?>">
			        <button type="button" class="btn btn-default" data-dismiss="modal"><?=$LANG['cancelar'];?></button>
			      </div>
			    </div>

			  </div>
			</div>
        </form>
        </div>
        <!--Bloco 2-->
        <div class="col-md-4">
            <!--Verificar atualização-->
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fa fa-wrench" aria-hidden="true"></i> <?=$LANG['atualizacao'];?></div>
                <div class="panel-body">
                    <?php
                      $versao = $vars['version'];
                        foreach (Capsule::table('tblconfiguration')->WHERE('setting', 'UpdaterLatestVersion')->get() as $system){
                	    	$versaowhmcs = $system->value;
                	    }
                      if($privacidade==1){
                            $urlcheck = "http://versao.whmcs.red/versao.php?codigo=validaccount&ref=".$urlsistema."&whmcsversao=".$versaowhmcs."&auth=1";
                      }
                      else{
                            $urlcheck = "http://versao.whmcs.red/versao.php?codigo=validaccount&auth=0";
                      }
		              $versaodisponivel = file_get_contents($urlcheck);
		                $json = json_decode($versaodisponivel, true);
                        $status_json = ($json['status']);
                        $versao_json = ($json['versao']);
		              
		              if($versao==$versao_json){
                        echo '<div class="alert alert-success" role="alert"><i class="fa fa-smile-o" aria-hidden="true"></i> '.$LANG["sucatualizacao"].'</div>';
		              }
		              else{
                        echo '<div class="alert alert-danger" role="alert"><i class="fa fa-frown-o" aria-hidden="true"></i> '.$LANG["erroatualizacao"].'</div>';
		                  echo '<center><a href="https://www.whmcs.red/download/valid-account/" class="btn btn-danger"><i class="fa fa-download" aria-hidden="true"></i> '.$LANG["baixar"].'</a></center>';
		              }
                    ?>
                </div>
            </div>
            <!--Configurações-->
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fa fa-cogs" aria-hidden="true"></i> <?=$LANG["configuracao"];?></div>
                <div class="panel-body">
                    <center><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#configuracoes"><i class="fa fa-cog" aria-hidden="true"></i> <?=$LANG['editar-config'];?></button></center>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>
<!--exibindo informações da consulta-->
<?php
//Verifica se ação esta sendo executada
if($_GET['acao']=='consultar'){
	//Verifica se a consulta foi para CPF
	if($_POST['tipo']=='cpf'){
		if($_POST['formabusca']=='existente'){ ?>
		<div class="panel panel-default">
		  <div class="panel-heading"><i class="fa fa-search" aria-hidden="true"></i> <?=$LANG["consulta"];?></div>
		  <div class="panel-body">
		    <div class="row">
			  <div class="col-md-8">
			  	<div class="panel panel-default">
				  <div class="panel-heading"><i class="fa fa-address-card" aria-hidden="true"></i> <?=$LANG["infotitular"];?></div>
				  <div class="panel-body">
				    <table class="table table-bordered">
					    <thead>
					      <tr>
					        <th><?=$LANG['tipo'];?></th>
					        <th><?=$LANG['info'];?></th>
					      </tr>
					    </thead>
					    <tbody>
					      <tr>
					        <td><?=$LANG['cpf'];?></td>
					        <td><?=$variaveldousuario[1];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["nomecompleto"];?></td>
					        <td><?=$verificarcpf['nome'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["nascimento"];?></td>
					        <td><?=$verificarcpf['nascimento'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["situacaocadastral"];?></td>
					        <td><?=$verificarcpf['situacao_cadastral'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["datasituacaocadastral"];?></td>
					        <td><?=$verificarcpf['situacao_cadastral_data'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["dataconsulta"];?></td>
					        <td><?=$verificarcpf['data_emissao'];?> ás <?=$verificarcpf['hora_emissao'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["codigocontrole"];?></td>
					        <td><?=$verificarcpf['codigo_controle'];?></td>
					      </tr>
					    </tbody>
					</table>
				  </div>
				</div>
			  </div>
			  <div class="col-md-4">
			  	<div class="panel panel-default">
				  <div class="panel-heading"><i class="fa fa-code-fork" aria-hidden="true"></i> <?=$LANG["acao"];?></div>
				  <div class="panel-body">
				    <center><a href="clientssummary.php?userid=<?=$variaveldousuario[0];?>" class="btn btn-primary"><i class="fa fa-user" aria-hidden="true"></i> <?=$LANG["vercadastro"];?></a><br/><br/><a href="supporttickets.php?action=open&userid=<?=$variaveldousuario[0];?>" class="btn btn-warning"><i class="fa fa-ticket" aria-hidden="true"></i> <?=$LANG["abrirticket"];?></a><br/><br/>
				    <a href="clientssummary.php?userid=<?=$variaveldousuario[0];?>&action=closeclient&token=<?=$token;?>" class="btn btn-danger"><i class="fa fa-lock" aria-hidden="true"></i> <?=$LANG["bloquearconta"];?></a></center>
				  </div>
				</div>
			  </div>
			</div>
		  </div>
		</div>
	 <? }
		if($_POST['formabusca']=='avulso'){ ?>
			<div class="panel panel-default">
			  <div class="panel-heading"><i class="fa fa-search" aria-hidden="true"></i> <?=$LANG["consulta"];?></div>
			  <div class="panel-body">
			    <div class="row">
				  <div class="col-md-8">
				  	<div class="panel panel-default">
					  <div class="panel-heading"><i class="fa fa-address-card" aria-hidden="true"></i> <?=$LANG["infotitular"];?></div>
					  <div class="panel-body">
					    <table class="table table-bordered">
						    <thead>
						      <tr>
						        <th><?=$LANG['tipo'];?></th>
						        <th><?=$LANG['info'];?></th>
						      </tr>
						    </thead>
						    <tbody>
						      <tr>
						        <td><?=$LANG['cpf'];?></td>
						        <td><?=$cpfavulso;?></td>
						      </tr>
						      <tr>
						        <td><?=$LANG["nomecompleto"];?></td>
						        <td><?=$verificarcpf['nome'];?></td>
						      </tr>
						      <tr>
						        <td><?=$LANG["nascimento"];?></td>
						        <td><?=$verificarcpf['nascimento'];?></td>
						      </tr>
						      <tr>
						        <td><?=$LANG["situacaocadastral"];?></td>
						        <td><?=$verificarcpf['situacao_cadastral'];?></td>
						      </tr>
						      <tr>
						        <td><?=$LANG["datasituacaocadastral"];?></td>
						        <td><?=$verificarcpf['situacao_cadastral_data'];?></td>
						      </tr>
						      <tr>
						        <td><?=$LANG["dataconsulta"];?></td>
						        <td><?=$verificarcpf['data_emissao'];?> ás <?=$verificarcpf['hora_emissao'];?></td>
						      </tr>
						      <tr>
						        <td><?=$LANG["codigocontrole"];?></td>
						        <td><?=$verificarcpf['codigo_controle'];?></td>
						      </tr>
						    </tbody>
						</table>
					  </div>
					</div>
				  </div>
				  <div class="col-md-4">
				  	<div class="panel panel-default">
					  <div class="panel-heading"><i class="fa fa-code-fork" aria-hidden="true"></i> <?=$LANG["acao"];?></div>
					  <div class="panel-body">
					    <center><b><?=$LANG["naoacoes"];?></b></center>
					  </div>
					</div>
				  </div>
				</div>
			  </div>
			</div>
		<? }
	}
	//Verifica se a consulta foi para CNPJ
	if($_POST['tipo']=='cnpj'){
		if($_POST['formabusca']=='existente'){ ?>
		<div class="panel panel-default">
		  <div class="panel-heading"><i class="fa fa-search" aria-hidden="true"></i> <?=$LANG["consulta"];?></div>
		  <div class="panel-body">
		    <div class="row">
			  <div class="col-md-8">
			  	<div class="panel panel-default">
				  <div class="panel-heading"><i class="fa fa-address-card" aria-hidden="true"></i> <?=$LANG["infoempresa"];?></div>
				  <div class="panel-body">
				    <table class="table table-bordered">
					    <thead>
					      <tr>
					        <th><?=$LANG['tipo'];?></th>
					        <th><?=$LANG['info'];?></th>
					      </tr>
					    </thead>
					    <tbody>
					      <tr>
					        <td><?=$LANG["cnpj"];?></td>
					        <td><?=$variaveldousuario[1];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["razaosocial"];?></td>
					        <td><?=$verificarcnpj['razao_social'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["nomefantasia"];?></td>
					        <td><?=$verificarcnpj['nome_fantasia'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["natureza"];?></td>
					        <td><?=$verificarcnpj['natureza_juridica'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["dataabertura"];?></td>
					        <td><?=$verificarcnpj['data_abertura'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["situacaocadastral"];?></td>
					        <td><?=$verificarcnpj['situacao_cadastral'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["atividadeprincipal"];?></td>
					        <td><?=$verificarcnpj['cnae_principal'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["atividadesecundaria"];?></td>
					        <td><? foreach ($verificarcnpj['cnaes_secundario'] as $atividade_secundaria){ echo ''.$atividade_secundaria.'<br/>'; }?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["email"];?></td>
					        <td><?=$verificarcnpj['email'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["telefone"];?></td>
					        <td><?=$verificarcnpj['telefone'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["endereco"];?></td>
					        <td><?=$verificarcnpj['logradouro'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["numero"];?></td>
					        <td><?=$verificarcnpj['numero'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["complemento"];?></td>
					        <td><?=$verificarcnpj['complemento'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["cidadeuf"];?></td>
					        <td><?=$verificarcnpj['cidade'];?>-<?=$verificarcnpj['uf'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["cep"];?></td>
					        <td><?=$verificarcnpj['cep'];?></td>
					      </tr>

					    </tbody>
					</table>
				  </div>
				</div>
			  </div>
			  <div class="col-md-4">
			  	<div class="panel panel-default">
				  <div class="panel-heading"><i class="fa fa-code-fork" aria-hidden="true"></i> <?=$LANG["acao"];?></div>
				  <div class="panel-body">
				    <center><a href="clientssummary.php?userid=<?=$variaveldousuario[0];?>" class="btn btn-primary"><i class="fa fa-user" aria-hidden="true"></i> <?=$LANG["vercadastro"];?></a><br/><br/><a href="supporttickets.php?action=open&userid=<?=$variaveldousuario[0];?>" class="btn btn-warning"><i class="fa fa-ticket" aria-hidden="true"></i> <?=$LANG["abrirticket"];?></a><br/><br/>
				    <a href="clientssummary.php?userid=<?=$variaveldousuario[0];?>&action=closeclient&token=<?=$token;?>" class="btn btn-danger"><i class="fa fa-lock" aria-hidden="true"></i> <?=$LANG["bloquearconta"];?></a></center>
				  </div>
				</div>
			  </div>
			</div>
		  </div>
		</div>
	 <? }
		if($_POST['formabusca']=='avulso'){ ?>
			<div class="panel panel-default">
			  <div class="panel-heading"><i class="fa fa-search" aria-hidden="true"></i> <?=$LANG["consulta"];?></div>
			  <div class="panel-body">
			    <div class="row">
				  <div class="col-md-8">
				  	<div class="panel panel-default">
					  <div class="panel-heading"><i class="fa fa-address-card" aria-hidden="true"></i> <?=$LANG["infoempresa"];?></div>
					  <div class="panel-body">
					    <table class="table table-bordered">
					    <thead>
					      <tr>
					        <th><?=$LANG['tipo'];?></th>
					        <th><?=$LANG['info'];?></th>
					      </tr>
					    </thead>
					    <tbody>
					      <tr>
					        <td><?=$LANG["cnpj"];?></td>
					        <td><?=$cnpjavulso;?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["razaosocial"];?></td>
					        <td><?=$verificarcnpj['razao_social'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["nomefantasia"];?></td>
					        <td><?=$verificarcnpj['nome_fantasia'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["natureza"];?></td>
					        <td><?=$verificarcnpj['natureza_juridica'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["dataabertura"];?></td>
					        <td><?=$verificarcnpj['data_abertura'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["situacaocadastral"];?></td>
					        <td><?=$verificarcnpj['situacao_cadastral'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["atividadeprincipal"];?></td>
					        <td><?=$verificarcnpj['cnae_principal'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["atividadesecundaria"];?></td>
					        <td><?=$verificarcnpj['cnaes_secundario'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["email"];?></td>
					        <td><?=$verificarcnpj['email'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["telefone"];?></td>
					        <td><?=$verificarcnpj['telefone'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["endereco"];?></td>
					        <td><?=$verificarcnpj['logradouro'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["numero"];?></td>
					        <td><?=$verificarcnpj['numero'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["complemento"];?></td>
					        <td><?=$verificarcnpj['complemento'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["cidadeuf"];?></td>
					        <td><?=$verificarcnpj['cidade'];?>-<?=$verificarcnpj['uf'];?></td>
					      </tr>
					      <tr>
					        <td><?=$LANG["cep"];?></td>
					        <td><?=$verificarcnpj['cep'];?></td>
					      </tr>

					    </tbody>
					</table>
					  </div>
					</div>
				  </div>
				  <div class="col-md-4">
				  	<div class="panel panel-default">
					  <div class="panel-heading"><i class="fa fa-code-fork" aria-hidden="true"></i> <?=$LANG["acao"];?></div>
					  <div class="panel-body">
					    <center><b><?=$LANG["naoacoes"];?></b></center>
					  </div>
					</div>
				  </div>
				</div>
			  </div>
			</div>
		<? }
	}
}
?>
<br/>
<div class="panel-footer"><?=$LANG["creditos"];?> - <a href="http://www.whmcs.red" target="_new">WHMCS.RED</a> & <a data-toggle="modal" data-target="#creditos"><?=$LANG['github'];?></a></div>

<!-- Modal Créditos-->
<div id="creditos" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?=$LANG['creditos'];?></h4>
      </div>
      <div class="modal-body">
        <p><?=$LANG['creditostexto'];?><br/><br/>
        <a href="https://github.com/jansenfelipe/cnpj-gratis" target="_new">CNPJ Grátis</a><br/>
        <a href="https://github.com/jansenfelipe/cpf-gratis" target="_new">CPF Grátis</a><br/>
        <a href="http://igorescobar.github.io/jQuery-Mask-Plugin/" target="_new">Jquery Mask Plugin</a><br/>
        <a href="https://github.com/tiagoporto/gerador-validador-cpf/" target="_new">Gerador e Validador de CPF</a></br>
        <a href="http://www.geradorcnpj.com/javascript-validar-cnpj.htm" target="_new">Validação de CNPJ por geradorcnpj.com</a><br/>
      	<br/>

        <?=$LANG['explicacaocredito'];?></p>
        <br/>
        <p><?=$LANG['maismodulos'];?> <a href="http://whmcs.red" target="_new">WHMCS.RED</a></p>
        <p><?=$LANG['forum'];?> <a href="http://forum.whmcs.red" target="_new">FORUM.WHMCS.RED</a></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?=$LANG['fechar'];?></button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Configuracoes-->
<div id="configuracoes" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-cogs" aria-hidden="true"></i> <?=$LANG['editar-config'];?></h4>
      </div>
      <form action="addonmodules.php?module=valid_account&config=salvar" method="POST">
	      <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <!--CPF Campo Customizado-->
                <div class="panel panel-default">
                  <div class="panel-heading"><?=$LANG['selecione-campo'];?> <b><?=$LANG['cpf'];?></b></div>
                  <div class="panel-body">
                    <select name="cpf" id="cpf" class="form-control">
                        <?php
                        $cpf_campo = '';
                        //Pegando informações da tabela do módulo.
                        /** @var stdClass $customfields */
                        foreach (Capsule::table('tblcustomfields')->get() as $customfields) {
                            $idfields = $customfields->id;
                            $namefields = $customfields->fieldname;                 
                                if($idfields==$cpfcampo){
                                    $cpf_campo .= '<option value="'.$idfields.'" selected="selected" style="background-color:#FFF" data-data="{"colour":"#FFF"}">'.$namefields.'</option>';
                                } 
                                else{
                                    $cpf_campo .= '<option value="'.$idfields.'" style="background-color:#FFF" data-data="{"colour":"#FFF"}">'.$namefields.'</option>';
                                }
                        }
                        
                        //imprime os resultados
                        echo $cpf_campo;               
                        ?>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <!--Data Nascimento Campo Customizado-->
                <div class="panel panel-default">
                  <div class="panel-heading"><?=$LANG['selecione-campo'];?> <b><?=$LANG["nascimento"];?></b></div>
                  <div class="panel-body">
                    <select name="data-nascimento" id="data-nascimento" class="form-control">
                        <?php
                        $datanascimento_campo = '';
                        //Pegando informações da tabela do módulo.
                        /** @var stdClass $customfields */
                        foreach (Capsule::table('tblcustomfields')->get() as $customfields) {
                            $idfields = $customfields->id;
                            $namefields = $customfields->fieldname;
                                if($idfields==$nascimentocampo){
                                    $datanascimento_campo .= '<option value="'.$idfields.'" selected="selected"  style="background-color:#FFF" data-data="{"colour":"#FFF"}">'.$namefields.'</option>';
                                } 
                                else{
                                    $datanascimento_campo .= '<option value="'.$idfields.'"  style="background-color:#FFF" data-data="{"colour":"#FFF"}">'.$namefields.'</option>';
                                }
                        }
                        
                        //imprime os resultados
                        echo $datanascimento_campo;            
                        ?>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <!--CNPJ Campo Customizado-->
                <div class="panel panel-default">
                  <div class="panel-heading"><?=$LANG['selecione-campo'];?> <b><?=$LANG["cnpj"];?></b></div>
                  <div class="panel-body">
                    <select name="cnpj" id="cnpj" class="form-control">
                        <?php
                        $cnpj_campo = '';
                        //Pegando informações da tabela do módulo.
                        /** @var stdClass $customfields */
                        foreach (Capsule::table('tblcustomfields')->get() as $customfields) {
                            $idfields = $customfields->id;
                            $namefields = $customfields->fieldname;
                                if($idfields==$cnpjcampo){
                                    $cnpj_campo .= '<option value="'.$idfields.'" selected="selected">'.$namefields.'</option>';
                                } 
                                else{
                                    $cnpj_campo .= '<option value="'.$idfields.'">'.$namefields.'</option>';
                                }
                        }
                        
                        //imprime os resultados
                        echo $cnpj_campo;              
                        ?>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <!--CPF Válido para Pessoa Jurídica-->
                <div class="panel panel-default">
                  <div class="panel-heading"><?=$LANG["juridicocpf"];?></div>
                  <div class="panel-body">
                    <select name="juridicocpf" id="juridicocpf" class="form-control">
                        <option value="1" <? if($juridicocpf=='1'){ echo 'selected="selected"'; }?>><?=$LANG["sim"];?></option>
                        <option value="2" <? if($juridicocpf=='2'){ echo 'selected="selected"'; }?>><?=$LANG["nao"];?></option>             
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <!--Tipo de Conta Campo Customizado-->
                <div class="panel panel-default">
                  <div class="panel-heading"><?=$LANG['selecione-campo'];?> <b><?=$LANG["tipoconta"];?></b></div>
                  <div class="panel-body">
                    <select name="tipoconta" id="tipoconta" class="form-control">
                        <?php
                        $tipoconta_campo = '';
                        //Pegando informações da tabela do módulo.
                        /** @var stdClass $customfields */
                        foreach (Capsule::table('tblcustomfields')->get() as $customfields) {
                            $idfields = $customfields->id;
                            $namefields = $customfields->fieldname;
                                if($idfields==$tipoconta){
                                    $tipoconta_campo .= '<option value="'.$idfields.'" selected="selected">'.$namefields.'</option>';
                                } 
                                else{
                                    $tipoconta_campo .= '<option value="'.$idfields.'">'.$namefields.'</option>';
                                }
                        }
                        
                        //imprime os resultados
                        echo $tipoconta_campo;             
                        ?>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <!--Documentacao-->
                <div class="panel panel-default">
                  <div class="panel-heading"><?=$LANG["sistemadedocumentacao"];?></div>
                  <div class="panel-body">
                    <select name="documentacao" id="documentacao" class="form-control">
                        <option value="1" <? if($documentacao=='1'){ echo 'selected="selected"'; }?>><?=$LANG["ativo"];?></option>
                        <option value="2" <? if($documentacao=='2'){ echo 'selected="selected"'; }?>><?=$LANG["desativado"];?></option>             
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <!--Alerta ClientArea-->
                <div class="panel panel-default">
                    <div class="panel-heading"><?=$LANG["alerta_documentacao"];?></div>
                    <div class="panel-body">
                        <select name="alerta_documentacao" id="alerta_documentacao" class="form-control">
                            <option value="1" <? if($alerta_documentacao=='1'){ echo 'selected="selected"'; }?>><?=$LANG["sim"];?></option>
                            <option value="2" <? if($alerta_documentacao=='2'){ echo 'selected="selected"'; }?>><?=$LANG["nao"];?></option>             
                        </select>
                    </div>
                </div>
              </div>
              <div class="col-md-6">
                <!--Alerta Email-->
                <div class="panel panel-default">
                    <div class="panel-heading"><?=$LANG["alerta_email"];?></div>
                    <div class="panel-body">
                        <select name="alerta_email" id="alerta_email" class="form-control">
                            <option value="1" <? if($alerta_email=='1'){ echo 'selected="selected"'; }?>><?=$LANG["sim"];?></option>
                            <option value="2" <? if($alerta_email=='2'){ echo 'selected="selected"'; }?>><?=$LANG["nao"];?></option>             
                        </select>
                    </div>
                </div>
              </div>
              <div class="col-md-6">
                <!--Template de E-mail Em Analise do Documento-->
                <div class="panel panel-default">
                    <div class="panel-heading"><?=$LANG["template_documento_emanalise"];?></div>
                    <div class="panel-body">
                        <select name="template_documentacao_emanalise" id="template_documentacao_emanalise" class="form-control">
                            <?
                            foreach(Capsule::table('tblemailtemplates')->WHERE('type','general')->get() as $template_email){
                                $name_email_d_ea = $template_email->name;
                                echo '<option value="'.$name_email_d_ea.'"'; if($template_documentacao_emanalise==$name_email_d_ea){ echo 'selected=""'; } echo'>'.$name_email_d_ea.'</option>';
                            }
                            ?>            
                        </select>
                    </div>
                </div>
              </div>
              <div class="col-md-6">
                <!--Template de E-mail Aprovado do Documento-->
                <div class="panel panel-default">
                    <div class="panel-heading"><?=$LANG["template_documento_aprovado"];?></div>
                    <div class="panel-body">
                        <select name="template_documentacao_aprovado" id="template_documentacao_aprovado" class="form-control">
                            <?
                            foreach(Capsule::table('tblemailtemplates')->WHERE('type','general')->get() as $template_email){
                                $name_email_d_ap = $template_email->name;
                                echo '<option value="'.$name_email_d_ap.'"'; if($template_documentacao_aprovado==$name_email_d_ap){ echo 'selected=""'; } echo'>'.$name_email_d_ap.'</option>';
                            }
                            ?>            
                        </select>
                    </div>
                </div>
              </div>
              <div class="col-md-6">
                <!--Template de E-mail Aprovado do Documento-->
                <div class="panel panel-default">
                    <div class="panel-heading"><?=$LANG["template_documento_reprovado"];?></div>
                    <div class="panel-body">
                        <select name="template_documentacao_reprovado" id="template_documentacao_reprovado" class="form-control">
                            <?
                            foreach(Capsule::table('tblemailtemplates')->WHERE('type','general')->get() as $template_email){
                                $name_email_d_re = $template_email->name;
                                echo '<option value="'.$name_email_d_re.'"'; if($template_documentacao_reprovado==$name_email_d_re){ echo 'selected=""'; } echo'>'.$name_email_d_re.'</option>';
                            }
                            ?>            
                        </select>
                    </div>
                </div>
              </div>
              <div class="col-md-6">
                <!--Template de E-mail Aprovado do Documento-->
                <div class="panel panel-default">
                    <div class="panel-heading"><?=$LANG["template_comprovante_emanalise"];?></div>
                    <div class="panel-body">
                        <select name="template_comprovante_emanalise" id="template_comprovante_emanalise" class="form-control">
                            <?
                            foreach(Capsule::table('tblemailtemplates')->WHERE('type','general')->get() as $template_email){
                                $name_email_c_ea = $template_email->name;
                                echo '<option value="'.$name_email_c_ea.'"'; if($template_comprovante_emanalise==$name_email_c_ea){ echo 'selected=""'; } echo'>'.$name_email_c_ea.'</option>';
                            }
                            ?>            
                        </select>
                    </div>
                </div>
              </div>
              <div class="col-md-6">
                <!--Template de E-mail Aprovado do Documento-->
                <div class="panel panel-default">
                    <div class="panel-heading"><?=$LANG["template_comprovante_aprovado"];?></div>
                    <div class="panel-body">
                        <select name="template_comprovante_aprovado" id="template_comprovante_aprovado" class="form-control">
                            <?
                            foreach(Capsule::table('tblemailtemplates')->WHERE('type','general')->get() as $template_email){
                                $name_email_c_ap = $template_email->name;
                                echo '<option value="'.$name_email_c_ap.'"'; if($template_comprovante_aprovado==$name_email_c_ap){ echo 'selected=""'; } echo'>'.$name_email_c_ap.'</option>';
                            }
                            ?>            
                        </select>
                    </div>
                </div>
              </div>
              <div class="col-md-6">
                  <!--Template de E-mail Aprovado do Documento-->
                <div class="panel panel-default">
                    <div class="panel-heading"><?=$LANG["template_comprovante_reprovado"];?></div>
                    <div class="panel-body">
                        <select name="template_comprovante_reprovado" id="template_comprovante_reprovado" class="form-control">
                            <?
                            foreach(Capsule::table('tblemailtemplates')->WHERE('type','general')->get() as $template_email){
                                $name_email_c_re = $template_email->name;
                                echo '<option value="'.$name_email_c_re.'"'; if($template_comprovante_reprovado==$name_email_c_re){ echo 'selected=""'; } echo'>'.$name_email_c_re.'</option>';
                            }
                            ?>            
                        </select>
                    </div>
                </div>
              </div>
              <div class="col-md-6">
                <!--Idade permitida-->
                <div class="panel panel-default">
                  <div class="panel-heading"><?=$LANG["idademinima"];?></div>
                  <div class="panel-body">
                    <div class="form-group">
                        <input name="idade" type="number" class="form-control" value="<?=$idadesistema;?>" />
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <!--Idade máxima permitida-->
                <div class="panel panel-default">
                  <div class="panel-heading"><?=$LANG["idademaxima"];?></div>
                  <div class="panel-body">
                    <div class="form-group">
                        <input name="idademaxima" type="number" class="form-control" value="<?=$idadesistemamaxima;?>" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading"><?=$LANG["customdiranexo"];?></div>
              <div class="panel-body">
                <div class="input-group">
                  <span class="input-group-addon"><?=$attachments_dir;?>/</span>
                  <input type="text" class="form-control" placeholder="<?=$LANG["customdiranexoplaceholde"];?>" name="attachments_pasta" id="attachments_pasta" value="<?=$attachments_pasta;?>">
                </div>
              </div>
            </div>
            <div class="panel panel-danger">
              <div class="panel-heading"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <?=$LANG["privacidade"];?></div>
              <div class="panel-body">
                <?=$LANG["privacidade_text"];?><br/>
                    <select name="privacidade" id="privacidade" class="form-control">
                        <option value="1" <? if($privacidade=='1'){ echo 'selected="selected"'; }?>><?=$LANG["sim"];?></option>
                        <option value="2" <? if($privacidade=='2'){ echo 'selected="selected"'; }?>><?=$LANG["nao"];?></option>             
                    </select>
                
              </div>
            </div>
	      </div>
	      <div class="modal-footer">
	        <input type="submit" class="btn btn-success" value="Salvar">
	        <button type="button" class="btn btn-default" data-dismiss="modal"><?=$LANG['cancelar'];?></button>
	      </div>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript" src="<?=$urlsistema;?>modules/addons/valid_account/js/jquery.mask.js"></script>
<script type="text/javascript">
//Função de mudar Busca
function mudarbusca(){
	if($("#formabusca").val() != "existente"){
		$("#usuarios").hide();
		$("#avulsopesquisa").show();
  	}
 	else{
   		$("#usuarios").show();
   		$("#avulsopesquisa").hide();
  	}
}
 	$("#formabusca").change(function(){mudarbusca();});
 	mudarbusca();

function mudarbuscaavulsa(){
	if($("#tipo").val() != "cpf" || $("#formabusca").val() != "avulso"){
		$("#avulsofisico").hide();
		$("#avulsojuridico").show();
		$('#usuarioscadastrados').empty();
  	}
 	else{
   		$("#avulsofisico").show();
   		$("#avulsojuridico").hide();
   		$('#usuarioscadastrados').empty();
  	}
}
 	$("#formabusca").change(function(){mudarbuscaavulsa();});
 	$("#tipo").change(function(){mudarbuscaavulsa();});
 	mudarbuscaavulsa();

function mudarconsulta(){
	if($("#tipo").val() === "cpf"){
		$('#usuarioscadastrados').empty();
		$("#usuarioscadastrados").append('<?=str_replace("\n", "", $option_pf);?>');
  	}
 	else{
   		$('#usuarioscadastrados').empty();
   		$("#usuarioscadastrados").append('<?=str_replace("\n", "", $option_pj);?>');
  	}
}
 	$("#tipo").change(function(){mudarconsulta();});
 	mudarconsulta();


//Formatação de Docuemnto
jQuery(function($){
	$("#cpf-avulso").mask("000.000.000-00", {reverse: true});
	$("#cnpj-avulso").mask("00.000.000/0000-00", {reverse: true});
	$("#nascimento-avulso").mask("00/00/0000");
});

//Alteração do Captcha da Imagem
$(document).ready(function(){
   $("#tipo").change(function(){
	    var tipo = $(this).val();
		if(tipo == "cpf"){
			$("#captchaimg").attr("src","<?=$paramscpf['captchaBase64'];?>");
		}else if(tipo == "cnpj"){
			$("#captchaimg").attr("src","<?=$paramscnpj['captchaBase64'];?>");
		}
   });
});
</script>
<? } ?>