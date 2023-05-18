<div class="panel" style="width: auto; text-align:center;">
{foreach from=$idd item=idp name=info} 
<div>
{if $imp == 0}
{if $overstock == 0}
		<p>
			<a href="{$urlenvio2}" id='{$id}' class="btn btn-primary" style="background-color:#00FF00;color:black">Actualizar stock LinkStore</a>
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
<input type="text" class="test" placeholder="{$commision}%" style="width: auto;margin-bottom: 10px;text-align: end;" id="nomecat[{$urlenvio3}]">
<input type="hidden" class="test" value="{$urlenvio3}">
<a href="#" class="btn btn-default" onclick="location = '{$urlenvio3}' + document.getElementById('nomecat[{$urlenvio3}]').value;">Commision</a>
</div>
{if !empty($idp.id_product)}
    {if !empty($catname)}
        Categoria: <strong>{$catname}</strong>
    {/if}
{/if}
{/foreach}
</div>