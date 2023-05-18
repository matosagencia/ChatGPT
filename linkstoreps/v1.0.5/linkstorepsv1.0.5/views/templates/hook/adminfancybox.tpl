{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2019 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel product-tab">
<h3>{l s='Ativar Linkstore' mod='linkstoreps'}</h3>


	<div class="form-group">
		<label class="control-label col-lg-3" for="simple_product">
			{l s='Ativar' mod='linkstoreps'}
		</label>
		<div class="col-lg-9">
			<div>
				{if $overstock == 0}
			<p>
				<a href="{$urlenvio4}1" class="btn btn-primary" style="background-color:#FFFF00;color:black">Usar stock Prestashop</a>
			</p>
		{else}
			<p>
				<a href="{$urlenvio4}0" class="btn btn-primary" style="background-color:#FFFF00;color:black">Usar stock LinkStore</a>
			</p>	
		{/if}
</div>
		</div>		
	</div>
</div>