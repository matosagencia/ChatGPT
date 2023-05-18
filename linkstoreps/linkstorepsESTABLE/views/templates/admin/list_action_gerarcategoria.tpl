	{foreach from=$idd item=idp name=info} 
{if !empty($idp.id_product)}
	{$catname}
{else}	
<select class='filter fixed-width' name='id_mercadolivre'  onchange='location = this.value;'>
	<option value="">Selecionar categoria</option>
	{foreach from=$idml item=item name=info} 
		{$item}
	{/foreach}
</select>
{/if}
{/foreach}