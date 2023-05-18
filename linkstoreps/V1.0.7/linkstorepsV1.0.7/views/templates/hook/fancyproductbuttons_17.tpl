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
{if $active == 1}
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.1/jquery.fancybox.pack.js" type="text/javascript"></script>
<script type="text/javascript">
{literal}
$(document).ready(function() {
	$(".fancyboxIframe").fancybox({
		maxWidth	: 900,
		maxHeight	: 600,
		fitToView	: false,
		width		: '90%',
		history		: false,
		height		: '90%',
		autoSize	: false,
		closeClick	: false,
		openEffect	: 'none',
		closeEffect	: 'none',
		iframe: {
				scrolling : 'auto',
			preload   : true
    }
	})
});
{/literal}
</script>

<style>
strong  {
color: #262626;
}

pre, .info {
font-size: smaller;
}

.info {
  list-style: none;
  display: inline;
}
.info:before {
  content: "NB: ";
  display: inline;
  float: left;
  margin-right: .5em;
}
</style>

<div class="container fluid" style="background-color:transparent;">
   
  <div ><a href="https://emporioconstruir.floori.io/?sku={$url}"  data-fancybox-type="iframe" class="btn btn-default fancyboxIframe"><img src="https://www.emporioconstruir.com.br/modules/fancybox/views/img/visualize.jpeg" alt="simulador"></a>
</div>
<link rel="stylesheet" href="http://fancyapps.com/fancybox/source/jquery.fancybox.css?v=2.1.3" type="text/css" media="screen" />
</br>

{/if}