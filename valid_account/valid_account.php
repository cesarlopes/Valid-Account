<?php
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
    'description' => 'Sistema de validação de cadastro baseado em CPF/CNPJ.',
    'version' => '0.6',
    'language' => 'portuguese-br',
    'author' => 'WHMCS.RED',
    );
    return $configarray;
}

function valid_account_activate($vars) {
    //Linguagem
    $LANG = $vars['_lang'];

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
	    }
	);

	//Inserindo dados no banco de dados
    Capsule::connection()->transaction(
        function ($connectionManager)
        {
            /** @var \Illuminate\Database\Connection $connectionManager */
            $connectionManager->table('mod_validaccount')->insert(['cpf' => 'nulo','data_nascimento' => 'nulo','cnpj' => 'nulo','tipoconta' => 'nulo','idade' => '18',]);
        }
    );

    //Retorno
    return array('status'=>'success','description'=>'Módulo Valid Account ativado com sucesso!');
    return array('status'=>'error','description'=>'Não foi possível ativar o módulo de Valid Account por causa de um erro desconhecido');
}
 
function valid_account_deactivate($vars) {
    //Linguagem
    $LANG = $vars['_lang'];
 
    //Remover Banco de Dados
	Capsule::schema()->drop('mod_validaccount');

    //Retorno
    return array('status'=>'success','description'=>'Módulo de Valid Account foi desativado com sucesso!');
    return array('status'=>'error','description'=>'Não foi possível desativar o módulo Valid Account por causa de um erro desconhecido');
}

function valid_account_output($vars){
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
			$updatedUserCount = Capsule::table('mod_validaccount')->update(['cpf' => $_POST['cpf'],'data_nascimento' => $_POST['data-nascimento'],'cnpj' => $_POST['cnpj'],'tipoconta' => $_POST['tipoconta'],]);
		    //Sucesso em salvar
		    echo '<div class="alert alert-success">'.$LANG["alertasalvar"].'</div>';
		}
		//Caso não conseguir, exibirá o erro
		catch (\Exception $e){
			echo '<div class="alert alert-danger">'.$LANG["alertasalvarerro"].' {$e->getMessage()}</div>';
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
					        $option_pj .= '<option value="'.$idusuario.'|'.$cnpjdados.'">'.$nomeempresa.' ('.$primeironome.' '.$segundonome.')</option>';
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
		              $versaodisponivel = file_get_contents("http://whmcs.red/versao/validaccount.txt");
		              if($versao==$versaodisponivel){
		                echo '<center><i class="fa fa-check-circle-o" aria-hidden="true"></i> '.$LANG["sucatualizacao"].'</center>';
		              }
		              else{
		                echo '<center><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> '.$LANG["erroatualizacao"].'<br/><a href="http://www.whmcs.red" class="btn btn-danger"><i class="fa fa-download" aria-hidden="true"></i> '.$LANG["baixar"].'</a></center>';
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
        <a href="http://www.geradorcpf.com/script-validar-cpf-php.htm" target="_new">Gerador de CPF (Validação de CPF por php)</a><br/>
        <a href="http://www.geradorcpf.com/script-validar-cpf-php.htm" target="_new">Gerador de CPF (Validação de CPF por php)</a><br/>
        <a href="https://www.todoespacoonline.com/w/2014/08/validar-cnpj-com-php/" target="_new">Todo Espaco Online (Validação de CNPJ por php)</a><br/>
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

<!-- Modal Créditos-->
<div id="configuracoes" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-cogs" aria-hidden="true"></i> <?=$LANG['editar-config'];?></h4>
      </div>
      <form action="addonmodules.php?module=valid_account&config=salvar" method="POST">
	      <div class="modal-body">
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
					            $cpf_campo .= '<option value="'.$idfields.'" selected="selected">'.$namefields.'</option>';
					        } 
					        else{
					            $cpf_campo .= '<option value="'.$idfields.'">'.$namefields.'</option>';
					        }
					}
					
					//imprime os resultados
					echo $cpf_campo;			   
			    	?>
                </select>
			  </div>
			</div>
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
					            $datanascimento_campo .= '<option value="'.$idfields.'" selected="selected">'.$namefields.'</option>';
					        } 
					        else{
					            $datanascimento_campo .= '<option value="'.$idfields.'">'.$namefields.'</option>';
					        }
					}
					
					//imprime os resultados
					echo $datanascimento_campo;			   
			    	?>
                </select>
			  </div>
			</div>
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
		$("#usuarioscadastrados").append('<option value="1">Arroz</option>');
  	}
 	else{
   		$("#avulsofisico").show();
   		$("#avulsojuridico").hide();
   		$('#usuarioscadastrados').empty();
   		$("#usuarioscadastrados").append('<option value="2">Feijão</option>');
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
   $("#nascimento-avulso").mask("00/00/0000");
   $("#cnpj-avulso").mask("00.000.000/0000-00", {reverse: true});
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