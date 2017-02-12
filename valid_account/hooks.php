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
		}
		//Criando o Javascript
		$javascript  = '';
		//Chamando o Jquery da Mascara
		$javascript .= '<script type="text/javascript" src="'.$urlsistema.'modules/addons/valid_account/jquery.maskedinput.min.js"></script>';
		//Verifica se o campo é o mesmo do CPF X CNPJ
		if($cpfcampo==$cnpjcampo){
			//Chamando as mascaras
			$javascript .= '<script type="text/javascript">jQuery(function($){ ';
			//Data de Nascimento
			$javascript .= '$("#customfield'.$nascimentocampo.'").mask("99/99/9999"); ';
			//Fechando Jquery das mascaras
			$javascript .= ' });</script>';
			//CPF CNPj mesmo campo
			$javascript .= '<script>jQuery(function($){$("#customfield'.$cpfcampo.'").focus(function(){$(this).unmask();$(this).val($(this).val().replace(/\D/g,""));}).click(function(){$(this).val($(this).val().replace(/\D/g,"")).unmask();}).blur(function(){if($(this).val().length==11){$(this).mask("999.999.999-99");}else if($(this).val().length==14){$(this).mask("99.999.999/9999-99");}});});</script>';
		}
		else{
			//Chamando as mascaras
			$javascript .= '<script type="text/javascript">jQuery(function($){ ';
			//CPF
			$javascript .= '$("#customfield'.$cpfcampo.'").mask("999.999.999-99"); ';
			//CNPJ
			$javascript .= '$("#customfield'.$cnpjcampo.'").mask("99.999.999/9999-99"); ';
			//Data de Nascimento
			$javascript .= '$("#customfield'.$nascimentocampo.'").mask("99/99/9999"); ';
			//Fechando Jquery das mascaras
			$javascript .= ' });</script>';
		}
		
		//Retorna o Javascript
		return $javascript;
	}
	//Adicionando o hook as páginas
	add_hook("ClientAreaFooterOutput",1,"valid_account");
?>