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
		$javascript .= '<script type="text/javascript" src="'.$urlsistema.'modules/addons/valid_account/js/jquery.maskedinput.min.js"></script>';
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
			//Valida O CPF
			$javascript .= '<script type="text/javascript">
$(document).ready(function() {
  var cpfvalor = $("#customfield'.$cpfcampo.'").val();
  var cnpjvalor = $("#customfield'.$cnpjcampo.'").val();
  $("#customfield3").attr("maxlength","14");
  $("#customfield3").on("change paste keyup", function() {
  	if(cpfvalor.length < 12){
    	if (cpfvalor != $("#customfield'.$cpfcampo.'").val()) {
	      	if ( CPF.validate($("#customfield'.$cpfcampo.'").val()) === true ) {
	      		$("#customfield'.$cpfcampo.'").parent().removeClass("has-error").addClass("has-success");
	      		$("input[type=submit]").removeAttr("disabled", "disabled");
		    }else{
			    $("#customfield'.$cpfcampo.'").parent().removeClass("has-success").addClass("has-error");
			    $("input[type=submit]").attr("disabled", "disabled");
			}
	      cpfvalor = $("#customfield'.$cpfcampo.'").val();
	    }
	}else{
		if (cnpjvalor != $("#customfield'.$cnpjcampo.'").val()) {
	      	if(validarCNPJ($("#customfield'.$cnpjcampo.'").val()) == true){
				$("#customfield'.$cnpjcampo.'").parent().removeClass("has-error").addClass("has-success");
	   			$("input[type=submit]").removeAttr("disabled", "disabled");
			}
			else{
				$("#customfield'.$cnpjcampo.'").parent().removeClass("has-success").addClass("has-error");
				$("input[type=submit]").attr("disabled", "disabled");
			}
	      cnpjvalor = $("#customfield'.$cnpjcampo.'").val();
	    }
		//$("#customfield3").parent().removeClass("has-error").removeClass("has-success");
	}
  });
});
    </script>';
		}else{
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
			//Valida O CPF
			$javascript .= '<script type="text/javascript">
$(document).ready(function() {
  var cpfvalor = $("#customfield'.$cpfcampo.'").val();
  $("#customfield'.$cpfcampo.'").on("change paste keyup", function() {
    if (cpfvalor != $("#customfield'.$cpfcampo.'").val()) {
      		if ( CPF.validate($("#customfield'.$cpfcampo.'").val()) === true ) {
      			$("#customfield'.$cpfcampo.'").parent().removeClass("has-error");
      			$("#customfield'.$cpfcampo.'").parent().addClass("has-success");';
      			if($requirecpf=='on'){
      				$javascript .= '$("input[type=submit]").removeAttr("disabled", "disabled");';
      			}
      		$javascript .= '
		    }else{
		    	$("#customfield'.$cpfcampo.'").parent().removeClass("has-success");
      			$("#customfield'.$cpfcampo.'").parent().addClass("has-error");';
      			if($requirecpf=='on'){
      				$javascript .= '$("input[type=submit]").attr("disabled", "disabled");';
      			}
      		$javascript .= '
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
      			$("#customfield'.$cnpjcampo.'").parent().addClass("has-success");';
      			if($requirecnpj=='on'){
      				$javascript .= '$("input[type=submit]").removeAttr("disabled", "disabled");';
      			}
		    $javascript .= '
		    }else{
		    	$("#customfield'.$cnpjcampo.'").parent().removeClass("has-success");
      			$("#customfield'.$cnpjcampo.'").parent().addClass("has-error");';
      			if($requirecnpj=='on'){
      				$javascript .= '$("input[type=submit]").attr("disabled", "disabled");';
      			}
      		$javascript .= '
		    }
      cnpjvalor = $("#customfield'.$cnpjcampo.'").val();
    }
  });
});
    </script>';
		}
		
		//Retorna o Javascript
		return $javascript;
	}
	//Adicionando o hook as páginas
	add_hook("ClientAreaFooterOutput",1,"valid_account");
?>