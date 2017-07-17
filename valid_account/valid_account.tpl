{if $statusdomod=="1"}
<div class="row">
  <div class="col-md-8"><p>{$validaccount_lang.valid_text_part1} <b>{$companyname}</b> {$validaccount_lang.valid_text_part2}</p></div>
  <div class="col-md-4"><center><font style="font-size:8em" class="fa fa-lock fa-5" aria-hidden="true"></font><font style="font-size:10px"><p>{$validaccount_lang.valid_text_part3}</p></font></center></div>
</div>
{if $smarty.get.va=="enviado"}
<div class="alert-message alert-message-success">
    <h4><i class="fa fa-check-circle" aria-hidden="true"></i> {$validaccount_lang.alerta_enviado}</h4>
    <p>{$validaccount_lang.alerta_enviado_texto}</p>
</div>
{/if}
{if $smarty.get.va=="tamanho"}
<div class="alert-message alert-message-danger">
    <h4><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> {$validaccount_lang.alerta_tamanho}</h4>
    <p>{$validaccount_lang.alerta_tamanho_texto}</p>
</div>
{/if}
{if $smarty.get.va=="extensao"}
<div class="alert-message alert-message-danger">
    <h4><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> {$validaccount_lang.alerta_extensao}</h4>
    <p>{$validaccount_lang.alerta_extensao_texto}</p>
</div>
{/if}
{if $smarty.get.va=="erro"}
<div class="alert-message alert-message-danger">
    <h4><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> {$validaccount_lang.alerta_erro}</h4>
    <p>{$validaccount_lang.alerta_erro_texto}</p>
</div>
{/if}
{if $smarty.get.va=="nulo"}
<div class="alert-message alert-message-danger">
    <h4><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> {$validaccount_lang.alerta_nulo}</h4>
    <p>{$validaccount_lang.alerta_nulo_texto}</p>
</div>
{/if}
{if $smarty.get.va=="indisponivel"}
<div class="alert-message alert-message-warning">
    <h4><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> {$validaccount_lang.alerta_indisponivel}</h4>
    <p>{$validaccount_lang.alerta_indisponivel_texto}</p>
</div>
{/if}
<form action="{$modulelink}" method="post" enctype="multipart/form-data">
<input type="hidden" name="postenviovalidaaccount" value="true">
<!--Documento-->
<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-8">{if $status_documento=="1"}<span class="label label-warning"><i class="fa fa-clock-o" aria-hidden="true"></i> {$validaccount_lang.aguardando_envio}</span>{/if}{if $status_documento=="2"}<span class="label label-primary"><i class="fa fa-search" aria-hidden="true"></i> {$validaccount_lang.em_analise}</span>{/if}{if $status_documento=="3"}<span class="label label-success"><i class="fa fa-check-square" aria-hidden="true"></i> {$validaccount_lang.aprovado}</span>{/if}{if $status_documento=="4"}<span class="label label-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> {$validaccount_lang.reprovado}</span> <a href="" class="btn btn-danger btn-xs" data-tooltip="tooltip" data-toggle="modal" data-target="#detalhes_rep_documento" data-placement="left" title="{$validaccount_lang.detalhesreprovacaotooltip}"><i class="fa fa-info-circle" aria-hidden="true"></i> {$validaccount_lang.detalhesreprovacao}</a>{/if}</div>
            <div class="col-md-4"><span class="pull-right"><a href="" class="btn btn-warning btn-xs" data-tooltip="tooltip" data-toggle="modal" data-target="#documento" data-placement="left" title="{$validaccount_lang.duvidas_doc}"><i class="fa fa-info-circle" aria-hidden="true"></i> {$validaccount_lang.duvidas}</a></span></div>
        </div>
    </div>
    <div class="panel-footer">
        <div class="row">
            <div class="col-md-8"><p><h3><i class="fa fa-id-card-o" aria-hidden="true"></i> {$validaccount_lang.documento_oficial}</h3></p></div>
            <div class="col-md-4">
                {if $status_inputfiledocumento=="0"}
                <div id="upload">
                    <input type="file" title="{$validaccount_lang.anexar_arquivo}" name="documento">
                </div>
                {else}
                <div id="upload">
                    <center>
                        <button class="btn btn-default" disabled=""  data-tooltip="tooltip" data-placement="left" title="{$validaccount_lang.placehole_anexar_arquivo}">{$validaccount_lang.anexar_arquivo}</button>
                    </center>
                </div>
                {/if}
            </div>
        </div>  
    </div>
</div>

<!--residência-->
<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-8">{if $status_residencia=="1"}<span class="label label-warning"><i class="fa fa-clock-o" aria-hidden="true"></i> {$validaccount_lang.aguardando_envio}</span>{/if}{if $status_residencia=="2"}<span class="label label-primary"><i class="fa fa-search" aria-hidden="true"></i> {$validaccount_lang.em_analise}</span>{/if}{if $status_residencia=="3"}<span class="label label-success"><i class="fa fa-check-square" aria-hidden="true"></i> {$validaccount_lang.aprovado}</span>{/if}{if $status_residencia=="4"}<span class="label label-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> {$validaccount_lang.reprovado}</span> <a href="" class="btn btn-danger btn-xs" data-tooltip="tooltip" data-toggle="modal" data-target="#detalhes_rep_comprovante" data-placement="left" title="{$validaccount_lang.detalhesreprovacaotooltip}"><i class="fa fa-info-circle" aria-hidden="true"></i> {$validaccount_lang.detalhesreprovacao}</a>{/if}</div>
            <div class="col-md-4"><span class="pull-right"><a href="" class="btn btn-warning btn-xs" data-tooltip="tooltip"  data-toggle="modal" data-target="#endereco" data-placement="left" title="{$validaccount_lang.duvidas_comp}"><i class="fa fa-info-circle" aria-hidden="true"></i> {$validaccount_lang.duvidas}</a></span></div>
        </div>
    </div>
    <div class="panel-footer">
        <div class="row">
            <div class="col-md-8"><p><h3><i class="fa fa-map-marker" aria-hidden="true"></i> {$validaccount_lang.comprovante_residencia_texto}</h3></p></div>
            <div class="col-md-4">
                {if $status_inputfileresidencia=="0"}
                <div id="upload">
                    <input type="file" title="{$validaccount_lang.anexar_arquivo}" name="comprovante">
                </div>
                {else}
                <div id="upload">
                    <center>
                        <button class="btn btn-default" disabled=""  data-tooltip="tooltip" data-placement="left" title="{$validaccount_lang.placehole_anexar_arquivo}">{$validaccount_lang.anexar_arquivo}</button>
                    </center>
                </div>
                {/if}
            </div>
        </div>  
    </div>
</div>
<button type="submit" class="btn btn-primary btn-block"><i class="fa fa-paper-plane" aria-hidden="true"></i> {$validaccount_lang.enviarbotao}</button>
</form>
<br/><br/>
<!--modal documento-->
<div id="documento" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-id-card-o" aria-hidden="true"></i> {$validaccount_lang.documento_oficial}</h4>
      </div>
      <div class="modal-body">
        <p><b>{$validaccount_lang.documentos_aceitos}</b></p>
        <ul class="list-group">
          <li class="list-group-item">{$validaccount_lang.rg}</li>
          <li class="list-group-item">{$validaccount_lang.carteira_motorista}</li>
        </ul>
        <p><b>{$validaccount_lang.extensoes_permitidas}</b></p>
        <ul class="list-group">
          <li class="list-group-item">JPG</li>
          <li class="list-group-item">JPEG</li>
          <li class="list-group-item">PNG</li>
          <li class="list-group-item">GIF</li>
          <li class="list-group-item">PDF</li>
        </ul>
        <p><b>{$validaccount_lang.tamanho_max_permitido}</b></p>
        <ul class="list-group">
          <li class="list-group-item">5MB</li>
        </ul>
        <p><b>{$validaccount_lang.observacoes}</b></p>
        <ul class="list-group">
          <li class="list-group-item">{$validaccount_lang.texto_ex_doc}</li>
          <li class="list-group-item">{$validaccount_lang.text_legivel}</li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-window-close-o" aria-hidden="true"></i> {$validaccount_lang.fecharbotao}</button>
      </div>
    </div>
  </div>
</div>
<!--modal endereço-->
<div id="endereco" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-map-marker" aria-hidden="true"></i> {$validaccount_lang.comprovante_residencia_texto}</h4>
      </div>
      <div class="modal-body">
        <p><b>{$validaccount_lang.listacomprovante_aceitos}</b></p>
        <ul class="list-group">
          <li class="list-group-item">{$validaccount_lang.textconta_agua}</li>
          <li class="list-group-item">{$validaccount_lang.textconta_luz}</li>
          <li class="list-group-item">{$validaccount_lang.textconta_telefone}</li>
          <li class="list-group-item">{$validaccount_lang.textconta_gas}</li>
          <li class="list-group-item">{$validaccount_lang.textconta_internet}</li>
          <li class="list-group-item">{$validaccount_lang.textconta_tv}</li>
          <li class="list-group-item">{$validaccount_lang.textconta_outros}</li>
        </ul>
        <p><b>{$validaccount_lang.extensoes_permitidas}</b></p>
        <ul class="list-group">
          <li class="list-group-item">JPG</li>
          <li class="list-group-item">JPEG</li>
          <li class="list-group-item">PNG</li>
          <li class="list-group-item">GIF</li>
          <li class="list-group-item">PDF</li>
        </ul>
        <p><b>{$validaccount_lang.tamanho_max_permitido}</b></p>
        <ul class="list-group">
          <li class="list-group-item">5MB</li>
        </ul>
        <p><b>{$validaccount_lang.observacoes}</b></p>
        <ul class="list-group">
          <li class="list-group-item">{$validaccount_lang.observacao_comptext1}</li>
          <li class="list-group-item">{$validaccount_lang.observacao_comptext2}</li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-window-close-o" aria-hidden="true"></i> {$validaccount_lang.fecharbotao}</button>
      </div>
    </div>
  </div>
</div>
{if $status_documento=="4"}
<!--Modal reprovacao documento-->
<div id="detalhes_rep_documento" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">{$validaccount_lang.detalhesreprovacao}</h4>
      </div>
      <div class="modal-body">
        <p>{$motivo_reprova_documento}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{$validaccount_lang.fecharbotao}</button>
      </div>
    </div>
  </div>
</div>
{/if}
{if $status_residencia=="4"}
<!--Modal reprovacao documento-->
<div id="detalhes_rep_comprovante" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">{$validaccount_lang.detalhesreprovacao}</h4>
      </div>
      <div class="modal-body">
        <p>{$motivo_reprova_comprovante}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{$validaccount_lang.fecharbotao}</button>
      </div>
    </div>
  </div>
</div>
{/if}

<style>
    #upload {
    margin-top: 10px;
    }
    /*Alerta Box*/
    .alert-message{
        margin: 20px 0;
        padding: 20px;
        border-left: 3px solid #eee;
    }
    .alert-message h4{
        margin-top: 0;
        margin-bottom: 5px;
    }
    .alert-message p:last-child{
        margin-bottom: 0;
    }
    .alert-message code{
        background-color: #fff;
        border-radius: 3px;
    }
    .alert-message-success{
        background-color: #F4FDF0;
        border-color: #3C763D;
    }
    .alert-message-success h4{
        color: #3C763D;
    }
    .alert-message-danger{
        background-color: #fdf7f7;
        border-color: #d9534f;
    }
    .alert-message-danger h4{
        color: #d9534f;
    }
    .alert-message-warning{
        background-color: #fcf8f2;
        border-color: #f0ad4e;
    }
    .alert-message-warning h4{
        color: #f0ad4e;
    }
    .alert-message-info{
        background-color: #f4f8fa;
        border-color: #5bc0de;
    }
    .alert-message-info h4{
        color: #5bc0de;
    }
    .alert-message-default{
        background-color: #EEE;
        border-color: #B4B4B4;
    }
    .alert-message-default h4{
        color: #000;
    }
    .alert-message-notice{
        background-color: #FCFCDD;
        border-color: #BDBD89;
    }
    .alert-message-notice h4{
        color: #444;
    }

    
</style>
<script type="text/javascript">
    $('[data-tooltip="tooltip"]').tooltip();
    $(document).ready(function(){
      $('input[type=file]').bootstrapFileInput();
    });
</script>
<script src="{$urlsistema}modules/addons/valid_account/js/bootstrap.file-input.js"></script>
{else}
<div class="alert-message alert-message-warning">
    <h4><i class="fa fa-info-circle" aria-hidden="true"></i> {$validaccount_lang.noacao}</h4>
    <p>{$validaccount_lang.noacao_text}</p>
</div>
<style>
    .alert-message{
        margin: 20px 0;
        padding: 20px;
        border-left: 3px solid #eee;
    }
    .alert-message h4{
        margin-top: 0;
        margin-bottom: 5px;
    }
    .alert-message p:last-child{
        margin-bottom: 0;
    }
    .alert-message code{
        background-color: #fff;
        border-radius: 3px;
    }
    .alert-message-success{
        background-color: #F4FDF0;
        border-color: #3C763D;
    }
    .alert-message-success h4{
        color: #3C763D;
    }
    .alert-message-danger{
        background-color: #fdf7f7;
        border-color: #d9534f;
    }
    .alert-message-danger h4{
        color: #d9534f;
    }
    .alert-message-warning{
        background-color: #fcf8f2;
        border-color: #f0ad4e;
    }
    .alert-message-warning h4{
        color: #f0ad4e;
    }
    .alert-message-info{
        background-color: #f4f8fa;
        border-color: #5bc0de;
    }
    .alert-message-info h4{
        color: #5bc0de;
    }
    .alert-message-default{
        background-color: #EEE;
        border-color: #B4B4B4;
    }
    .alert-message-default h4{
        color: #000;
    }
    .alert-message-notice{
        background-color: #FCFCDD;
        border-color: #BDBD89;
    }
    .alert-message-notice h4{
        color: #444;
    }
</style>
{/if}