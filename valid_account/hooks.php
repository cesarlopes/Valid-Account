<?php
//Laravel DataBase
use WHMCS\Database\Capsule;
//Menu Item
use WHMCS\View\Menu\Item as MenuItem;
//Client Alert
use WHMCS\User\Alert;
//Bloqueia o acesso direto ao arquivo
if (!defined("WHMCS")){
    die("Acesso restrito!");
}
	//Cria o Hook
	function valid_account($vars) {
		    //URL Do Sistema
    		//Pegando URL do sistema no banco
    		foreach (Capsule::table('tblconfiguration')->WHERE('setting', 'SystemURL')->get() as $system){
	    		$urlsistema = $system->value;
			}
		//Pegando informações da tabela do módulo.
		/** @var stdClass $cvallid */
		foreach (Capsule::table('mod_validaccount')->get() as $cvallid){
		    $cpfcampo = $cvallid->cpf;
		    $nascimentocampo = $cvallid->data_nascimento;
		    $cnpjcampo = $cvallid->cnpj;
		    $tipoconta = $cvallid->tipoconta;
		    $juridicocpf = $cvallid->juridicocpf;
		}
		//Verifica se é obrigatório o campo de CPF
		foreach (Capsule::table('tblcustomfields')->WHERE('id', $cpfcampo)->get() as $customfieldsrequirecpf){
			$requirecpf = $customfieldsrequirecpf->required;
		}
		//Verifica se é obrigatório o campo de Data de Nascimento
		foreach (Capsule::table('tblcustomfields')->WHERE('id', $nascimentocampo)->get() as $customfieldsrequirenascimento){
			$requirenascimento = $customfieldsrequirenascimento->required;
		}
		//Verifica se é obrigatório o campo de CNPJ
		foreach (Capsule::table('tblcustomfields')->WHERE('id', $cnpjcampo)->get() as $customfieldsrequirecnpj){
			$requirecnpj = $customfieldsrequirecnpj->required;
		}
		//Criando o Javascript
		$javascript  = '';
		//Script de validação de CPF
		$javascript .= '<script type="text/javascript" src="'.$urlsistema.'modules/addons/valid_account/js/CPF.js"></script>';
		//Script de validação de CNPJ
		$javascript .= '<script type="text/javascript" src="'.$urlsistema.'modules/addons/valid_account/js/CNPJ.js"></script>';
		//Chamando o Jquery da Mascara
		$javascript .= '<script type="text/javascript" src="'.$urlsistema.'modules/addons/valid_account/js/jquery.mask.js"></script>';
		//Verifica se o campo é o mesmo do CPF X CNPJ
		if($cpfcampo==$cnpjcampo){
			//Chamando as mascaras
			$javascript .= '<script type="text/javascript">jQuery(function($){ ';
			//Data de Nascimento
			$javascript .= '$("#customfield'.$nascimentocampo.'").mask("00/00/0000"); ';
			//Fechando Jquery das mascaras
			$javascript .= ' });</script>';
			//Validação de campo igual para CPf e CNPJ
			$javascript .= '<script type="text/javascript">var cpfCnpj = function (val) {
    return val.length > 14 ? "00.000.000/0000-00" : "000.000.000-009";
},
optionsDocumento = {onKeyPress: function(val, e, field, options) {
    field.mask(cpfCnpj(val), options);
    }
};
$("#customfield'.$cpfcampo.'").mask(cpfCnpj, optionsDocumento);</script>';
			//Valida O CPF
			$javascript .= '<script type="text/javascript">
//Remover o resultado Nenhum de campo de tipo de conta
$("#customfield'.$tipoconta.'").find("option").eq(0).remove();
//Ativar o Requerimento do campo de data de nascimento
$("#customfield'.$nascimentocampo.'").prop("required",true);
$(document).ready(function() {
  var cpfvalor = $("#customfield'.$cpfcampo.'").val();
  var cnpjvalor = $("#customfield'.$cnpjcampo.'").val();
  $("#customfield'.$cpfcampo.'").on("change paste keyup", function() {
  	if(cpfvalor.length < 15){
    	if (cpfvalor != $("#customfield'.$cpfcampo.'").val()) {
	      	if ( CPF.validate($("#customfield'.$cpfcampo.'").val()) === true ) {
	      		$("#customfield'.$cpfcampo.'").parent().removeClass("has-error").addClass("has-success");
		    }else{
			    $("#customfield'.$cpfcampo.'").parent().removeClass("has-success").addClass("has-error");
			}
	      cpfvalor = $("#customfield'.$cpfcampo.'").val();
	    }
	}else{
		if (cnpjvalor != $("#customfield'.$cnpjcampo.'").val()) {
	      	if(validarCNPJ($("#customfield'.$cnpjcampo.'").val()) == true){
				$("#customfield'.$cnpjcampo.'").parent().removeClass("has-error").addClass("has-success");
			}
			else{
				$("#customfield'.$cnpjcampo.'").parent().removeClass("has-success").addClass("has-error");
			}
	      cnpjvalor = $("#customfield'.$cnpjcampo.'").val();
	    }
		//$("#customfield'.$cpfcampo.'").parent().removeClass("has-error").removeClass("has-success");
	}
  });
});
    </script>';
		}else{
			//Chamando as mascaras
			$javascript .= '<script type="text/javascript">jQuery(function($){ ';
			//CPF
			$javascript .= '$("#customfield'.$cpfcampo.'").mask("000.000.000-00", {reverse: true}); ';
			//CNPJ
			$javascript .= '$("#customfield'.$cnpjcampo.'").mask("00.000.000/0000-00", {reverse: true}); ';
			//Data de Nascimento
			$javascript .= '$("#customfield'.$nascimentocampo.'").mask("00/00/0000"); ';
			//Fechando Jquery das mascaras
			$javascript .= ' });</script>';
			//Valida O CPF
			$javascript .= '<script type="text/javascript">
//Remover o resultado Nenhum de campo de tipo de conta
$("#customfield'.$tipoconta.'").find("option").eq(0).remove();
//Ativar o Requerimento do campo de data de nascimento
$("#customfield'.$nascimentocampo.'").prop("required",true);
//Função dos campos de cpf e cnpj a baixo
$(document).ready(function() {
  var cpfvalor = $("#customfield'.$cpfcampo.'").val();
  $("#customfield'.$cpfcampo.'").on("change paste keyup", function() {
    if (cpfvalor != $("#customfield'.$cpfcampo.'").val()) {
      		if ( CPF.validate($("#customfield'.$cpfcampo.'").val()) === true ) {
      			$("#customfield'.$cpfcampo.'").parent().removeClass("has-error");
      			$("#customfield'.$cpfcampo.'").parent().addClass("has-success");
		    }else{
		    	$("#customfield'.$cpfcampo.'").parent().removeClass("has-success");
      			$("#customfield'.$cpfcampo.'").parent().addClass("has-error");
		    }
      cpfvalor = $("#customfield'.$cpfcampo.'").val();
    }
  });
});
$(document).ready(function() {
  var cnpjvalor = $("#customfield'.$cnpjcampo.'").val();
  $("#customfield'.$cnpjcampo.'").on("change paste keyup", function() {
    if (cnpjvalor != $("#customfield'.$cnpjcampo.'").val()) {
      		if(validarCNPJ($("#customfield'.$cnpjcampo.'").val()) == true){
      			$("#customfield'.$cnpjcampo.'").parent().removeClass("has-error");
      			$("#customfield'.$cnpjcampo.'").parent().addClass("has-success");
		    }else{
		    	$("#customfield'.$cnpjcampo.'").parent().removeClass("has-success");
      			$("#customfield'.$cnpjcampo.'").parent().addClass("has-error");
		    }
      cnpjvalor = $("#customfield'.$cnpjcampo.'").val();
    }
  });
});
';
if($_GET['a']=='checkout'){
$javascript .= '
	function valid_account_pais_cart(){
		if($("#inputCountry").val() != "BR"){
		    $("#customfield'.$cpfcampo.'").prop("disabled", true);
		   	$("#customfield'.$cnpjcampo.'").prop("disabled", true);
		   	$("#customfield'.$tipoconta.'").prop("disabled", true);
		}
		else{
		    $("#customfield'.$cpfcampo.'").prop("disabled", false);
		   	$("#customfield'.$cnpjcampo.'").prop("disabled", false);
		   	$("#customfield'.$tipoconta.'").prop("disabled", false);
		}
 	}
 $("#inputCountry").change(function(){valid_account_pais_cart();});
 valid_account_pais_cart();
';
} else{
$javascript .= '
 	function valid_account_pais(){
		if($("#country").val() != "BR"){
			$("#customfield'.$cpfcampo.'").prop("disabled", true);
		   	$("#customfield'.$cnpjcampo.'").prop("disabled", true);
		   	$("#customfield'.$tipoconta.'").prop("disabled", true);
		}
		else{
			$("#customfield'.$cpfcampo.'").prop("disabled", false);
		   	$("#customfield'.$cnpjcampo.'").prop("disabled", false);
		   	$("#customfield'.$tipoconta.'").prop("disabled", false);
		}
 	}
 $("#country").change(function(){valid_account_pais();});
 valid_account_pais();
';
}
$javascript .= '
 	function valid_account_tipoconta(){
		if($("#customfield'.$tipoconta.'").val() != "Pessoa Física"){';
			if($cpfcampo==$cnpjcampo){
				$javascript .= '$("#customfield'.$cnpjcampo.'").prop("required",true);';
				$javascript .= '$("#companyname").prop("required",true);';
			}
			else{
				if($juridicocpf=='1'){
					$javascript .= '$("#customfield'.$cpfcampo.'").prop("disabled", false);';
					$javascript .= '$("#customfield'.$cpfcampo.'").prop("required",true);';
				}
				else{
					$javascript .= '$("#customfield'.$cpfcampo.'").prop("disabled", true);';
					$javascript .= '$("#customfield'.$cpfcampo.'").prop("required",false);';
				}
				$javascript .= '$("#customfield'.$cnpjcampo.'").prop("disabled", false);';
				$javascript .= '$("#customfield'.$cnpjcampo.'").prop("required",true);';
				$javascript .= '$("#companyname").prop("required",true);';
			}
		$javascript .= '
		}
		else{';
			if($cpfcampo==$cnpjcampo){
				$javascript .= '$("#customfield'.$cpfcampo.'").prop("required",true);';
				$javascript .= '$("#companyname").prop("required",false);';
			}
			else{
				$javascript .= '$("#customfield'.$cpfcampo.'").prop("disabled", false);';
				$javascript .= '$("#customfield'.$cnpjcampo.'").prop("disabled", true);';
				$javascript .= '$("#customfield'.$cnpjcampo.'").prop("required",false);';
				$javascript .= '$("#customfield'.$cpfcampo.'").prop("required",true);';
				$javascript .= '$("#companyname").prop("required",false);';
			}
		$javascript .= '
		}
 	}
 $("#customfield'.$tipoconta.'").change(function(){valid_account_tipoconta();});
 valid_account_tipoconta();
</script>';
		}
		//Retorna o Javascript
		return $javascript;
	}

	//Adicionando o hook as páginas de cart e register
	add_hook("ClientAreaFooterOutput",1,"valid_account");
	add_hook("AfterShoppingCartCheckout",1,"valid_account");

	//Função de validar campos com PHP
	function valid_account_validacao($vars){
		//Recebendo alguns dados do módulo
		foreach (Capsule::table('mod_validaccount')->get() as $cvallid){
		    $cpfcampo = $cvallid->cpf;
		    $nascimentocampo = $cvallid->data_nascimento;
		    $cnpjcampo = $cvallid->cnpj;
		    $tipoconta = $cvallid->tipoconta;
		    $idadesistema = $cvallid->idade;
		    $idadesistemamaxima = $cvallid->idademaxima;
		    $juridicocpf = $cvallid->juridicocpf;
		}
		//recebendo os dados do custom fields
		$pais = $vars["country"];
		$cpf =  $vars['customfield'][$cpfcampo];
		$cnpj = $vars['customfield'][$cnpjcampo];
		$nascimento = $vars['customfield'][$nascimentocampo];
		$tipodeconta = $vars['customfield'][$tipoconta];
		//Cria a função de validação de CPF em PHP
		function CPF ($ncpf = null) {
		    // Verifica se um número foi informado
		    if(empty($ncpf)) {
		        return false;
		    }
		    // Elimina possivel mascara
		    $ncpf = preg_replace("/\D+/","",$ncpf);
		    $ncpf = str_pad($ncpf, 11, '0', STR_PAD_LEFT);
		    // Verifica se o numero de digitos informados é igual a 11 
		    if (strlen($ncpf) != 11) {
		        return false;
		    }
		    // Verifica se nenhuma das sequências invalidas abaixo 
		    // foi digitada. Caso afirmativo, retorna falso
		    else if ($ncpf == '00000000000' || 
		        $ncpf == '11111111111' || 
		        $ncpf == '22222222222' || 
		        $ncpf == '33333333333' || 
		        $ncpf == '44444444444' || 
		        $ncpf == '55555555555' || 
		        $ncpf == '66666666666' || 
		        $ncpf == '77777777777' || 
		        $ncpf == '88888888888' || 
		        $ncpf == '99999999999') {
		        return false;
		     // Calcula os digitos verificadores para verificar se o
		     // CPF é válido
		     } else {   
		        for ($t = 9; $t < 11; $t++) {
		            for ($d = 0, $c = 0; $c < $t; $c++) {
		                $d += $ncpf{$c} * (($t + 1) - $c);
		            }
		            $d = ((10 * $d) % 11) % 10;
		            if ($ncpf{$c} != $d) {
		                return false;
		            }
		        }
		        return true;
		    }
		}
		//Validação de CNPJ
		function CNPJ($ncnpj){
		    // Deixa o CNPJ com apenas números
		    $ncnpj = preg_replace( '/[^0-9]/', '', $ncnpj );
		    // Garante que o CNPJ é uma string
		    $ncnpj = (string)$ncnpj;
		    // O valor original
		    $ncnpj_original = $ncnpj;
		    // Captura os primeiros 12 números do CNPJ
		    $primeiros_numeros_cnpj = substr( $ncnpj, 0, 12 );
		    /**
		     * Multiplicação do CNPJ
		     *
		     * @param string $ncnpj Os digitos do CNPJ
		     * @param int $posicoes A posição que vai iniciar a regressão
		     * @return int O
		     *
		     */
		    if ( ! function_exists('multiplica_cnpj') ) {
		        function multiplica_cnpj( $ncnpj, $posicao = 5 ) {
		            // Variável para o cálculo
		            $calculo = 0;
		            
		            // Laço para percorrer os item do cnpj
		            for ( $i = 0; $i < strlen( $ncnpj ); $i++ ) {
		                // Cálculo mais posição do CNPJ * a posição
		                $calculo = $calculo + ( $ncnpj[$i] * $posicao );
		                // Decrementa a posição a cada volta do laço
		                $posicao--;
		                // Se a posição for menor que 2, ela se torna 9
		                if ( $posicao < 2 ) {
		                    $posicao = 9;
		                }
		            }
		            // Retorna o cálculo
		            return $calculo;
		        }
		    }
		    // Faz o primeiro cálculo
		    $primeiro_calculo = multiplica_cnpj( $primeiros_numeros_cnpj );
		    // Se o resto da divisão entre o primeiro cálculo e 11 for menor que 2, o primeiro
		    // Dígito é zero (0), caso contrário é 11 - o resto da divisão entre o cálculo e 11
		    $primeiro_digito = ( $primeiro_calculo % 11 ) < 2 ? 0 :  11 - ( $primeiro_calculo % 11 );
		    // Concatena o primeiro dígito nos 12 primeiros números do CNPJ
		    // Agora temos 13 números aqui
		    $primeiros_numeros_cnpj .= $primeiro_digito;
		    // O segundo cálculo é a mesma coisa do primeiro, porém, começa na posição 6
		    $segundo_calculo = multiplica_cnpj( $primeiros_numeros_cnpj, 6 );
		    $segundo_digito = ( $segundo_calculo % 11 ) < 2 ? 0 :  11 - ( $segundo_calculo % 11 );
		    // Concatena o segundo dígito ao CNPJ
		    $ncnpj = $primeiros_numeros_cnpj . $segundo_digito;
		    // Verifica se o CNPJ gerado é idêntico ao enviado
		    if ( $ncnpj === $ncnpj_original ) {
		        return true;
		    }
		}
		//Validação de Data de Nascimento
		function idade($idade){
			list($dia,$mes,$ano) = explode("/",$idade);
			$dia_res  = date("d") - $dia;
			$mes_res  = date("m") - $mes;
			$ano_res  = date("Y") - $ano;
			if($mes_res < 0) $ano_res--;
			elseif (($mes_res==0) && ($dia_res < 0)) $ano_res--;
			return $ano_res;
		}

		//Verificando se o pais é o Brasil para ser obrigatório o CPF ou CNPJ
		if($pais=="BR"){
		    //Verifica se a edição não é de um admin
		    if($_SESSION['adminid']==""){
		        //Verifica se não for cliente prossegue o codigo
		        if($_SESSION['uid']==""){
        			//verificando tipo de conta
        			if($tipodeconta=="Pessoa Física"){
        				//valida o CPF
        				if( CPF($cpf) ){
        					//Consulta se o CPF já não é cadastrado no sistema
        					$existente = Capsule::table('tblcustomfieldsvalues')->WHERE('fieldid', $cpfcampo)->WHERE('value', $cpf)->count();
        					//Verifica se existe algum cadastro com o CPF
        					if($existente=='0'){
        						//Verificando a data de nascimento se é uma data permitida
        						if(idade($nascimento)>=$idadesistema){
        							//Verifica a idade máxima agora
        							if(idade($nascimento)<=$idadesistemamaxima){
        							    //Silêncio
        						    }
        						    //Caso for maior a idade do que permitido ele retorna o erro
        						    else{
        						        $erro = "Desculpe, mas não é permitido cadastros com idade superior a ".$idadesistemamaxima." anos.";
        							    return $erro;
        						    }
        						}
        						//Caso não for retorna o erro
        						else{
        							$erro = "Desculpe, mas não é permitido cadastros com idade inferior a ".$idadesistema." anos.";
        							return $erro;
        						}
        					}
        					//Caso tiver conta existente para o CPF ele notifica e retorna ao cadastro
        					else{
        						$erro = "O CPF informado já existe conta associada, entre em contato para maiores informações";
        						return $erro;
        					}
        				}
        				else{
        					//trava o cadastro e retorna como CPF inválido
        					$erro = "O CPF informado é inválido!";
        					return $erro;
        				}
        			}
        			//Verificando o tipod de conta se é jurídica
        			if($tipodeconta=="Pessoa Jurídica"){
        				//valida o CNPJ
        				if(CNPJ($cnpj)==true){
        					//Consulta se o CPF já não é cadastrado no sistema
        					$existentecnpj = Capsule::table('tblcustomfieldsvalues')->WHERE('fieldid', $cnpjcampo)->WHERE('value', $cnpj)->count();
        					//Verifica se existe algum cadastro com o CPF
        					if($existentecnpj=='0'){
        						//Verifica se não é campo unico
        						if($cpfcampo==$cnpjcampo){}
        						else{
        							//Verifica se é obrigatório o CPF na conta PJ
        							if($juridicocpf=='1'){
        								//valida o CPF
        								if( CPF($cpf) ){
        									//Consulta se o CPF já não é cadastrado no sistema
        									$existentecpf = Capsule::table('tblcustomfieldsvalues')->WHERE('fieldid', $cpfcampo)->WHERE('value', $cpf)->count();
        									//Verifica se existe algum cadastro com o CPF
        									if($existentecpf=='0'){
        										//Verificando a data de nascimento se é uma data permitida
        										if(idade($nascimento)>=$idadesistema){
        											//Silêncio
        										}
        										//Caso não for retorna o erro
        										else{
        											$erro = "Desculpe, mas não é permitido cadastros com idade inferior a ".$idadesistema." anos.";
        											return $erro;
        										}
        									}
        									//Caso tiver conta existente para o CPF ele notifica e retorna ao cadastro
        									else{
        										$erro = "O CPF informado já existe conta associada, entre em contato para maiores informações";
        										return $erro;
        									}
        								}
        								else{
        									//trava o cadastro e retorna como CPF inválido
        									$erro = "O CPF informado é inválido!";
        									return $erro;
        								}
        
        							}
        							//continua sem retorno
        							else{
        								//Silêncio
        							}
        						}
        						
        					}
        					//Caso tiver conta existente para o CNPJ ele notifica e retorna ao cadastro
        					else{
        						$erro = "O CNPJ informado possui registros de conta existente em uso, entre em contato para maiores informações";
        						return $erro;
        					}
        				}
        				else{
        					//trava o cadastro e retorna como CPF inválido
        					$erro = "O CNPJ informado é inválido!";
        					return $erro;
        				}
        			}
		        }
		        //Verifica se for cliente logado
		        else{
		            //Consulta o BD
		            	foreach (Capsule::table('mod_validaccount')->get() as $cvallid){
                		    $cpfcampo = $cvallid->cpf;
                		    $nascimentocampo = $cvallid->data_nascimento;
                		    $cnpjcampo = $cvallid->cnpj;
                		    $tipoconta = $cvallid->tipoconta;
                		    $juridicocpf = $cvallid->juridicocpf;
                		}
		            foreach(Capsule::table('tblcustomfieldsvalues')->WHERE('fieldid', $cpfcampo)->WHERE('relid', $_SESSION['uid'])->get() as $cpfanterior){
		                $cpf_antes = $cpfanterior->value;
		            }
		            foreach(Capsule::table('tblcustomfieldsvalues')->WHERE('fieldid', $cnpjcampo)->WHERE('relid', $_SESSION['uid'])->get() as $cnpjanterior){
		                $cnpj_antes = $cnpjanterior->value;
		            }
		            //$valorcnpj = Capsule::table('tblcustomfieldsvalues')->WHERE('fieldid', $cnpjcampo)->WHERE('relid', $_SESSION['uid'])->get();
		            //verifica o cpf e cnpj
		            if($cpf!=$cpf_antes or $cnpj!=$cnpj_antes){
            			//verificando tipo de conta
            			if($tipodeconta=="Pessoa Física"){
            				//valida o CPF
            				if( CPF($cpf) ){
            					//Consulta se o CPF já não é cadastrado no sistema
            					$existente = Capsule::table('tblcustomfieldsvalues')->WHERE('fieldid', $cpfcampo)->WHERE('value', $cpf)->count();
            					//Verifica se existe algum cadastro com o CPF
            					if($existente=='0'){
            						//Verificando a data de nascimento se é uma data permitida
            						if(idade($nascimento)>=$idadesistema){
            							//Verifica a idade máxima agora
            							if(idade($nascimento)<=$idadesistemamaxima){
            							    //Silêncio
            						    }
            						    //Caso for maior a idade do que permitido ele retorna o erro
            						    else{
            						        $erro = "Desculpe, mas não é permitido cadastros com idade superior a ".$idadesistemamaxima." anos.";
            							    return $erro;
            						    }
            						}
            						//Caso não for retorna o erro
            						else{
            							$erro = "Desculpe, mas não é permitido cadastros com idade inferior a ".$idadesistema." anos.";
            							return $erro;
            						}
            					}
            					//Caso tiver conta existente para o CPF ele notifica e retorna ao cadastro
            					else{
            						$erro = "O CPF informado já existe conta associada, entre em contato para maiores informações";
            						return $erro;
            					}
            				}
            				else{
            					//trava o cadastro e retorna como CPF inválido
            					$erro = "O CPF informado é inválido!";
            					return $erro;
            				}
            			}
            			//Verificando o tipod de conta se é jurídica
            			if($tipodeconta=="Pessoa Jurídica"){
            				//valida o CNPJ
            				if(CNPJ($cnpj)==true){
            					//Consulta se o CPF já não é cadastrado no sistema
            					$existentecnpj = Capsule::table('tblcustomfieldsvalues')->WHERE('fieldid', $cnpjcampo)->WHERE('value', $cnpj)->count();
            					//Verifica se existe algum cadastro com o CPF
            					if($existentecnpj=='0'){
            						//Verifica se não é campo unico
            						if($cpfcampo==$cnpjcampo){}
            						else{
            							//Verifica se é obrigatório o CPF na conta PJ
            							if($juridicocpf=='1'){
            								//valida o CPF
            								if( CPF($cpf) ){
            									//Consulta se o CPF já não é cadastrado no sistema
            									$existentecpf = Capsule::table('tblcustomfieldsvalues')->WHERE('fieldid', $cpfcampo)->WHERE('value', $cpf)->count();
            									//Verifica se existe algum cadastro com o CPF
            									if($existentecpf=='0'){
            										//Verificando a data de nascimento se é uma data permitida
            										if(idade($nascimento)>=$idadesistema){
            											//Silêncio
            										}
            										//Caso não for retorna o erro
            										else{
            											$erro = "Desculpe, mas não é permitido cadastros com idade inferior a ".$idadesistema." anos.";
            											return $erro;
            										}
            									}
            									//Caso tiver conta existente para o CPF ele notifica e retorna ao cadastro
            									else{
            										$erro = "O CPF informado já existe conta associada, entre em contato para maiores informações";
            										return $erro;
            									}
            								}
            								else{
            									//trava o cadastro e retorna como CPF inválido
            									$erro = "O CPF informado é inválido!";
            									return $erro;
            								}
            
            							}
            							//continua sem retorno
            							else{
            								//Silêncio
            							}
            						}
            						
            					}
            					//Caso tiver conta existente para o CNPJ ele notifica e retorna ao cadastro
            					else{
            						$erro = "O CNPJ informado possui registros de conta existente em uso, entre em contato para maiores informações";
            						return $erro;
            					}
            				}
            				else{
            					//trava o cadastro e retorna como CPF inválido
            					$erro = "O CNPJ informado é inválido!";
            					return $erro;
            				}
            			}
		            }
		            //
		            else{
		              //silencio  
		            }
		        }
		    }
		}
		//Caso não for do brasil prossegue sem nenhuma ação
		else{
			//Silêncio
		}

		//Validando CPF
		//Verificando se já não existe
	}
	//Adicionando a função de validação em segundo passo
	add_hook("ClientDetailsValidation",1,"valid_account_validacao");

if(Capsule::schema()->hasTable('mod_validaccount')){
    foreach (Capsule::table('mod_validaccount')->get() as $cvallid){
        $documentacao = $cvallid->documentacao;
        $documentacao_alerta = $cvallid->alerta_documentacao;
        $attachments_pasta = $cvallid->attachments_pasta;
    }
if($documentacao=='1'){
    //Adicionando Menu de validação de documentos
    add_hook('ClientAreaSecondaryNavbar', 1, function (MenuItem $SecondaryNavbar){
        $client = Menu::context('client');
        if (!is_null($client)){
            if (!is_null($SecondaryNavbar->getChild('Account'))) {
                $SecondaryNavbar->getChild('Account')
                    ->addChild('Valid Account', array(
                        'label' => 'Valid Account',
                        'uri' => 'index.php?m=valid_account',
                        'order' => '55',
                    ));
            }
        }
    });
    //Mensagem de Status no summary do admin(cliente)
    function valid_account_documentos_status($vars){
        //UserID
        $id_user = $_GET['userid'];
        //contando resultados dos documentos se existem
        $totaldocumento = Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $id_user)->WHERE('tipo', '0')->count();
        $totalresidencia = Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $id_user)->WHERE('tipo', '1')->count();
        
        //Verificando os documentos
        if($totaldocumento==0){
            $resultadodoc = '<span class="label label-warning"><i class="fa fa-clock-o" aria-hidden="true"></i> Aguardando envio</span>';
        }
        //Caso houver algum resultado, vai ver o status
        else{
            //BD
            foreach(Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $id_user)->WHERE('tipo', '0')->get() as $documento){
                $status_documento = $documento->status;
            }
            if($status_documento==2){
                $resultadodoc = '<span class="label label-primary"><i class="fa fa-search" aria-hidden="true"></i> Em Analise</span>';
            }
            if($status_documento==3){
                $resultadodoc = '<span class="label label-success"><i class="fa fa-check-square" aria-hidden="true"></i> Aprovado</span>';
            }
            if($status_documento==4){
                $resultadodoc = '<span class="label label-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Reprovado</span>';
            }
            
        }
        //Verificando Endereço
        if($totalresidencia==0){
            $resultadoend = '<span class="label label-warning"><i class="fa fa-clock-o" aria-hidden="true"></i> Aguardando envio</span>';
        }
        //Caso houver resultados prossegue aqui
        else{
            //BD
            foreach(Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $id_user)->WHERE('tipo', '1')->get() as $endereco){
                $status_residencia = $endereco->status;
            }
            if($status_residencia==2){
                $resultadoend = '<span class="label label-primary"><i class="fa fa-search" aria-hidden="true"></i> Em Analise</span>';
            }
            if($status_residencia==3){
                $resultadoend = '<span class="label label-success"><i class="fa fa-check-square" aria-hidden="true"></i> Aprovado</span>';
            }
            if($status_residencia==4){
                $resultadoend = '<span class="label label-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Reprovado</span>';
            }
        }
        
        return "<div class='alert alert-info' role='alert'><i class='fa fa-lock' aria-hidden=true'></i> <b>Valid Account:</b> [Documento] ".$resultadodoc." | [Comprovante de Residência] ".$resultadoend."</div>";
    }
    //add hook
    add_hook("AdminAreaClientSummaryPage",1,"valid_account_documentos_status");
    
    //link modal summary action link admin
    function valid_account_actionlinks($vars){
        $linksaction = [];

        $linksaction[] = '<p><b><i class="fa fa-lock" aria-hidden="true"></i> Valid Account</b></p>';
        $linksaction[] = '<a href="#" data-toggle="modal" data-target="#documento"><i class="fa fa-id-card-o" aria-hidden="true"></i> Documento de Identidade</a>';
        $linksaction[] = '<a href="#" data-toggle="modal" data-target="#residencia"><i class="fa fa-map-marker" aria-hidden="true"></i> Comprovante de Residência</a>';
        $linksaction[] = '<br/>';


        return $linksaction;
    }
    //Add Hook
    add_hook("AdminAreaClientSummaryActionLinks",1,"valid_account_actionlinks");
    
    //Modal Action Link
    function valid_account_modalacctionlinks($vars){
        global $customadminpath;
        global $attachments_dir;
            foreach (Capsule::table('tblconfiguration')->WHERE('setting', 'SystemURL')->get() as $system){
	    		$urlsistema = $system->value;
			}
			
        $id_user = $_GET['userid'];
        $totaldocumento = Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $id_user)->WHERE('tipo', '0')->count();
        $totalresidencia = Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $id_user)->WHERE('tipo', '1')->count();
        
        //Salvamento de dados
        if($_GET['va']=='download'){
            if(isset($_GET['iddownload'])){
                //recebendo GET
                $id_down = $_GET['iddownload'];
                //Consulta da pasta base do attachments
                foreach (Capsule::table('mod_validaccount')->get() as $cvallid){
                    $attachments_pasta = $cvallid->attachments_pasta;
                }
                //Arquivo a ser baixado
                $arquivo = ''.$attachments_dir.'/'.$attachments_pasta.'/'.$id_down.'';
                if(file_exists($arquivo)) {
                    $arquivo_nome = basename($arquivo);
                    $arquivo_size = filesize($arquivo);
            
                    //Output header
                    header("Cache-Control: private");
                    header("Content-Type: application/stream");
                    header("Content-Length: ".$arquivo_size);
                    header("Content-Disposition: attachment; filename=".$arquivo_nome);
            
                    //Saida do Arquivo.
                    readfile ($arquivo);                   
                    exit();
                }
                else {
                    die('Arquivo inválido.');
                }
            }
            else{
                die('Arquivo inválido.');
            }
        }
        if($_GET['va']=='salvar'){
            $data_atual = date('d/m/Y H:i:s');
            if($_POST['funcao']!="" && $_POST['validade']!=""){
                    //bd valid
            		 foreach (Capsule::table('mod_validaccount')->get() as $cvallid){
                        $alerta_email = $cvallid->alerta_email;
                	    $template_documentacao_emanalise = $cvallid->template_documentacao_emanalise;
                        $template_documentacao_aprovado = $cvallid->template_documentacao_aprovado;
                        $template_documentacao_reprovado = $cvallid->template_documentacao_reprovado;
                	    $template_comprovante_emanalise = $cvallid->template_comprovante_emanalise;
                        $template_comprovante_aprovado = $cvallid->template_comprovante_aprovado;
                        $template_comprovante_reprovado = $cvallid->template_comprovante_reprovado;
                    }
                try{
                    $updatedUserCount = Capsule::table('mod_validaccount_documentos')->WHERE('id', $_POST['bdid'])->WHERE('usuario', $id_user)->WHERE('tipo', $_POST['funcao'])->update(['status' => $_POST['validade'],'data_aprovacao' => $data_atual,'motivo_status' => $_POST['motivo'],]);
                    if($alerta_email==1){
                        if($_POST['validade']==2 && $_POST['funcao']==0){
                            $email_template = $template_documentacao_emanalise;
                        }
                        if($_POST['validade']==2 && $_POST['funcao']==1){
                            $email_template = $template_comprovante_emanalise;
                        }
                        if($_POST['validade']==3 && $_POST['funcao']==0){
                            $email_template = $template_documentacao_aprovado;
                        }
                        if($_POST['validade']==3 && $_POST['funcao']==1){
                            $email_template = $template_comprovante_aprovado;
                        }
                        if($_POST['validade']==4 && $_POST['funcao']==0){
                            $email_template = $template_documentacao_reprovado;
                        }
                        if($_POST['validade']==4 && $_POST['funcao']==1){
                            $email_template = $template_comprovante_reprovado;
                        }
                        $admconsulta = Capsule::table('tbladmins')->WHERE('id', $_SESSION['adminid'])->get();
                        $administrador = $admconsulta->username;
                        $valores["id"] = $id_user;
                        //Email a ser enviado
                        $valores["messagename"] = $email_template;
                        //Comando a ser executado na função
                        $comando = "sendemail";
                        //executa comando
                        $executar = localAPI($comando, $valores, $administrador);
                    }
                    header("Location: ".$urlsistema."".$customadminpath."/clientssummary.php?userid=".$id_user."&va=sucesso");
                }
                catch (\Exception $e){
                    header("Location: ".$urlsistema."".$customadminpath."/clientssummary.php?userid=".$id_user."&va=erro");
                }
            }
            else{
                header("Location: ".$urlsistema."".$customadminpath."/clientssummary.php?userid=".$id_user."&va=invalido&".$_GET['va']."");
            }
        }
        //Modals
        echo '<div id="documento" class="modal fade" role="dialog">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-id-card-o" aria-hidden="true"></i> Documento de Identidade</h4>
                  </div>
                  <form action="'.$urlsistema.''.$customadminpath.'/clientssummary.php?userid='.$id_user.'&va=salvar" method="post">
                  <div class="modal-body">';
                    if($totaldocumento==0){
                        echo '<div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Nenhum documento enviado até o momento!</div>';
                    }
                    else{
                        foreach(Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $id_user)->WHERE('tipo', '0')->get() as $documento_bd){
                            $bdid_doc = $documento_bd->id;
                            $status_doc = $documento_bd->status;
                            $arquivo_doc = $documento_bd->arquivo;
                            $data_apro_doc = $documento_bd->data_aprovacao;
                            $motivo_status_docbd = $documento_bd->motivo_status;
                        }
                         echo '<div class="form-group">
                                <label for="validade">Validação de Documento Oficial</label>
                                <select name="validade" id="validade" class="form-control">
                                  <option value="2"'; if($status_doc=='2'){ echo 'selected=""'; } echo'>Em Analise</option>
                                  <option value="3"'; if($status_doc=='3'){ echo 'selected=""'; } echo'>Aprovar</option>
                                  <option value="4"'; if($status_doc=='4'){ echo 'selected=""'; } echo'>Reprovar</option>
                                </select>
                              </div>
                              <div class="form-group">
                                <label for="motivo">Motivo para Reprovação</label>
                                <input type="text" class="form-control" name="motivo" id="motivo" placeholder="Caso for reprovado, adicionado detalhes da causa da reprovação" value="'.$motivo_status_docbd.'">
                              </div>
                              <br/>';
                              if($status_doc==3){
                                echo '<p><b>Data da Aprovação:</b> '.$data_apro_doc.'</p>';
                              }
                              echo '
                              <br/>
                              <label for="modomanutencao">Arquivo Enviado:</label>
                              <a href="'.$urlsistema.''.$customadminpath.'/clientssummary.php?userid='.$id_user.'&va=download&iddownload='.$arquivo_doc.'" target="_new" class="btn btn-default" data-toggle="tooltip" data-placement="right" title="Baixar Arquivo"><i class="fa fa-download" aria-hidden="true"></i> Download de Arquivo</a>';
                    }         
                  echo'
                  <input type="hidden" name="funcao" value="0">
                  <input type="hidden" name="bdid" value="'.$bdid_doc.'">
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-success"'; if($totaldocumento==0){ echo 'disabled=""'; } echo'><i class="fa fa-floppy-o" aria-hidden="true"></i> Salvar</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                  </div>
                  </form>
                </div>
              </div>
            </div>
            
            <div id="residencia" class="modal fade" role="dialog">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-map-marker" aria-hidden="true"></i> Comprovante de Residência</h4>
                  </div>
                  <form action="'.$urlsistema.''.$customadminpath.'/clientssummary.php?userid='.$id_user.'&va=salvar" method="post">
                  <div class="modal-body">';
                    if($totalresidencia==0){
                        echo '<div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Nenhum comprovante enviado até o momento!</div>';
                    }
                    else{
                        foreach(Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $id_user)->WHERE('tipo', '1')->get() as $residencia_bd){
                            $bdid_res = $residencia_bd->id;
                            $status_res = $residencia_bd->status;
                            $arquivo_res = $residencia_bd->arquivo;
                            $data_apro_res = $residencia_bd->data_aprovacao;
                            $motivo_status_resbd = $residencia_bd->motivo_status;
                        }
                        echo '<div class="form-group">
                                <label for="validade">Validação de Comprovante de Residência</label>
                                <select name="validade" id="validade" class="form-control">
                                  <option value="2"'; if($status_res=='2'){ echo 'selected=""'; } echo'>Em Analise</option>
                                  <option value="3"'; if($status_res=='3'){ echo 'selected=""'; } echo'>Aprovar</option>
                                  <option value="4"'; if($status_res=='4'){ echo 'selected=""'; } echo'>Reprovar</option>
                                </select>
                              </div>
                              <div class="form-group">
                                <label for="motivo">Motivo para Reprovação</label>
                                <input type="text" class="form-control" name="motivo" id="motivo" placeholder="Caso for reprovado, adicionado detalhes da causa da reprovação" value="'.$motivo_status_resbd.'">
                              </div>
                              <br/>';
                              if($status_res==3){
                                echo '<p><b>Data da Aprovação:</b> '.$data_apro_res.'</p>';
                              }
                              echo '
                              <br/>
                              <label for="modomanutencao">Arquivo Enviado:</label>
                              <a href="'.$urlsistema.''.$customadminpath.'/clientssummary.php?userid='.$id_user.'&va=download&iddownload='.$arquivo_res.'" target="_new" class="btn btn-default" data-toggle="tooltip" data-placement="right" title="Baixar Arquivo"><i class="fa fa-download" aria-hidden="true"></i> Download de Arquivo</a>';
                    }         
                  echo'
                  <input type="hidden" name="funcao" value="1">
                  <input type="hidden" name="bdid" value="'.$bdid_res.'">
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-success"'; if($totalresidencia==0){ echo 'disabled=""'; } echo'><i class="fa fa-floppy-o" aria-hidden="true"></i> Salvar</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                  </div>
                  </form>
                </div>
              </div>
            </div>';
            
            //Mensagens de erro
            if($_GET['va']=="sucesso"){
                echo '<div class="alert alert-success" role="alert"><i class="fa fa-check-circle" aria-hidden="true"></i> Informações atualizadas com sucesso!</div>';
            }
            if($_GET['va']=="erro"){
                echo '<div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Não foi possível salvar seus dados devido a um erro interno!</div>';
            }
            if($_GET['va']=="invalido"){
                echo '<div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Não foi possível slavar seus dados devido aos dados serem inválidos!</div>';
            }
            
    }
    add_hook("AdminAreaClientSummaryPage",1,"valid_account_modalacctionlinks");
    //Alerta de Documentação
    if($documentacao_alerta==1){
        //Mensagem Area do Cliente Valid Account
        function areacliente_validaccount($vars){
            //Linguagem
            include('modules/addons/valid_account/lang/portuguese-br.php');
            //URL SYSTEM
            foreach (Capsule::table('tblconfiguration')->WHERE('setting', 'SystemURL')->get() as $system){
    	        $urlsistema = $system->value;
    		}
            //Consulta dados
            $id_user = $_SESSION['uid'];
            $totaldocumento = Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $id_user)->WHERE('tipo', '0')->count();
            $totalresidencia = Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $id_user)->WHERE('tipo', '1')->count();
            //BOX
            if($totaldocumento=="0" or $totalresidencia=="0"){
               $mensagem .= '<div class="alert-message alert-message-warning">
        <h4><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> '.$_ADDONLANG['oops-clientarea'].'</h4>
        <p>'.$_ADDONLANG['oops-clientarea-msg'].'</p>
        <a href="'.$urlsistema.'index.php?m=valid_account" class="btn btn-warning"><i class="fa fa-external-link" aria-hidden="true"></i> '.$_ADDONLANG['oops-clientarea-button'].'</a>
    </div>'; 
            }
            else{
                //quando haver algum documento vai verificar
                //Verifica o documento
                foreach(Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $id_user)->WHERE('tipo', '0')->get() as $documentodb){
                    $status_doc_mg = $documentodb->status;
                }
                //Verifica o comprovante
                foreach(Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $id_user)->WHERE('tipo', '1')->get() as $comprovantedb){
                    $status_comp_mg = $comprovantedb->status;
                }
                if($status_doc_mg=="4" or $status_comp_mg=="4"){
                    $mensagem .= '<div class="alert-message alert-message-warning">
        <h4><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> '.$_ADDONLANG['oops-clientarea-error4'].'</h4>
        <p>'.$_ADDONLANG['oops-clientarea-error4-text'].'</p>
        <a href="'.$urlsistema.'index.php?m=valid_account" class="btn btn-warning"><i class="fa fa-external-link" aria-hidden="true"></i> '.$_ADDONLANG['oops-clientarea-button'].'</a>
    </div>'; 
                }
            }
            
            //CSS
            $mensagem .= "<style>#upload{margin-top:10px}.alert-message{margin:20px 0;padding:20px;border-left:3px solid #eee}.alert-message h4{margin-top:0;margin-bottom:5px}.alert-message p:last-child{margin-bottom:0}.alert-message code{background-color:#fff;border-radius:3px}.alert-message-success{background-color:#F4FDF0;border-color:#3C763D}.alert-message-success h4{color:#3C763D}.alert-message-danger{background-color:#fdf7f7;border-color:#d9534f}.alert-message-danger h4{color:#d9534f}.alert-message-warning{background-color:#fcf8f2;border-color:#f0ad4e}.alert-message-warning h4{color:#f0ad4e}.alert-message-info{background-color:#f4f8fa;border-color:#5bc0de}.alert-message-info h4{color:#5bc0de}.alert-message-default{background-color:#EEE;border-color:#B4B4B4}.alert-message-default h4{color:#000}.alert-message-notice{background-color:#FCFCDD;border-color:#BDBD89}.alert-message-notice h4{color:#444}
    </style>";
            
            return $mensagem;
        }
        add_hook("ClientAreaHomepage",1,"areacliente_validaccount");
    }
    //Client Alert
    function clientalert_validaccount($vars){
        //Linguagem
        include('modules/addons/valid_account/lang/portuguese-br.php');
        //vars
        $nome = $vars->firstName;
        $sobrenome = $vars->lastName;
        //Verificações
        $id_user = $_SESSION['uid'];
        $totaldocumento = Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $id_user)->WHERE('tipo', '0')->count();
        $totalresidencia = Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $id_user)->WHERE('tipo', '1')->count();
        //verifica se não há resultados
        if($totaldocumento=="0" or $totalresidencia=="0"){
            //criando alert
            return new Alert("<b>{$nome}</b> ".$_ADDONLANG['alertclient_base']."",'warning','index.php?m=valid_account');
        }
        else{
            //Verifica o documento
            foreach(Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $id_user)->WHERE('tipo', '0')->get() as $documentodb){
                $status_doc_mg = $documentodb->status;
            }
            //Verifica o comprovante
            foreach(Capsule::table('mod_validaccount_documentos')->WHERE('usuario', $id_user)->WHERE('tipo', '1')->get() as $comprovantedb){
                $status_comp_mg = $comprovantedb->status;
            }
           if($status_doc_mg=="4" or $status_comp_mg=="4"){
               //criando alert
                return new Alert("<b>{$nome}</b> ".$_ADDONLANG['alertclient_base']."",'danger','index.php?m=valid_account');
           }  
        }
        
        
        
        
    }
    add_hook("ClientAlert",1,"clientalert_validaccount");
}

}

?>