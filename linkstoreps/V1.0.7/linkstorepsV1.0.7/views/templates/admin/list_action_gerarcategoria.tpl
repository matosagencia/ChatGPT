<div class="panel" style="width: auto;">
{foreach from=$idd item=idp name=info} 
<div>  
{if $imp == 0} 
{if $overstock == 0}
		<p>
			<a href="{$urlenvio2}" id='{$id}' class="btn btn-primary" style="background-color:#00FF00;color:black" >Actualizar stock LinkStore</a>
		</p>
		<p>
			<a href="{$urlenvio4}1" id='{$id}' class="btn btn-primary" style="background-color:#FFFF00;color:black">Usar stock Prestashop</a> 
		</p>
	{else}
		<p>
			<a href="{$urlenvio4}0" id='{$id}' class="btn btn-primary" style="background-color:#FFFF00;color:black">Usar stock LinkStore</a>
		</p>	
	{/if}
{/if}
</div>
<div>
<input type="text" class="test" placeholder="%" style="width: auto;margin-bottom: 10px;text-align: end;" id="nomecat[{$urlenvio3}]">
<input type="hidden" class="test" value="{$urlenvio3}">
<a href="#" class="btn btn-default" onclick="location = '{$urlenvio3}' + document.getElementById('nomecat[{$urlenvio3}]').value;">Commision</a>
</div>
{if !empty($idp.id_product)}
    {if !empty($catname)}
        Categoria: <strong>{$catname}</strong>
    {/if}
{else}	
<div class="autocomplete-search">   
  <input type="text" class="form-control search" id="{$id}" placeholder="procure la categoria" autocomplete="off">
  <button id='urlenvio{$id}' class="btn btn-default" onclick='location = this.value;'>Salvar Categoria</button>
</div>

{literal}
<script type="text/javascript">
$(document).ready(function(){
    var data =[
        {/literal}{foreach from=$idml item=item name=info}{literal} 
		
        {'label':'{/literal}{$item.name}{literal}','value':'{/literal}{$urlenvio}={$item.id_category}{literal}'},
        {/literal}
	{/foreach}{literal}]; 

    $("#{/literal}{$id}{literal}").autocomplete({
        source:data,
        select: function(e, ui) {
            e.preventDefault() // <--- Prevent the value from being inserted.
            $("#urlenvio{/literal}{$id}{literal}").val(ui.item.value);
            $(this).val(ui.item.label);
        }
    });
    //alert("this loaded");
});
</script>
{/literal}

{/if}
{/foreach}
</div>