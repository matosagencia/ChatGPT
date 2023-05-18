{foreach from=$idd item=idp name=info} 
{if !empty($idp.id_product)}
	{$catname}
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