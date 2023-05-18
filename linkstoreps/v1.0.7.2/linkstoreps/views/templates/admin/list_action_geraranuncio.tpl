{if $href == ''}
<span class="btn btn-default">
	{$action}
</span>	
{else}	
<a href="{$href}" title="{$action}" class="btn btn-default">
	<i class="icon-pencil"></i> {$action}
</a>
{/if}