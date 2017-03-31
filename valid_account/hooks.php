<?php
//Laravel DataBase
use WHMCS\Database\Capsule;
//Bloqueia o acesso direto ao arquivo
if (!defined("WHMCS"))
	{
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
 	function valid_account_tipoconta(){
		if($("#customfield'.$tipoconta.'").val() != "Pessoa Física"){';
			if($cpfcampo==$cnpjcampo){
				$javascript .= '$("#customfield'.$cnpjcampo.'").prop("required",true);';
				$javascript .= '$("#companyname").prop("required",true);';
			}
			else{
				$javascript .= '$("#customfield'.$cpfcampo.'").prop("disabled", true);';
				$javascript .= '$("#customfield'.$cnpjcampo.'").prop("disabled", false);';
				$javascript .= '$("#customfield'.$cnpjcampo.'").prop("required",true);';
				$javascript .= '$("#customfield'.$cpfcampo.'").prop("required",false);';
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
		    $ncpf = ereg_replace('[^0-9]', '', $ncpf);
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
		function CNPJ ( $ncnpj ) {
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
			//Verificando o tipod de conta se é jurídica
			if($tipodeconta=="Pessoa Jurídica"){
				//valida o CNPJ
				if(CNPJ($cnpj)==true){
					//Consulta se o CPF já não é cadastrado no sistema
					$existente = Capsule::table('tblcustomfieldsvalues')->WHERE('fieldid', $cnpjcampo)->WHERE('value', $cnpj)->count();
					//Verifica se existe algum cadastro com o CPF
					if($existente=='0'){
						//Silêncio
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
		//Caso não for do brasil prossegue sem nenhuma ação
		else{
			//Silêncio
		}

		//Validando CPF
		//Verificando se já não existe
	}
	//Adicionando a função de validação em segundo passo
	add_hook("ClientDetailsValidation",1,"valid_account_validacao");
?>