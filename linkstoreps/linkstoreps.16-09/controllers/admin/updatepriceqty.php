<?php
include '../../../../config/config.inc.php';

// Função de porcentagem: Quanto é X% de N?
function porcentagem_xn ( $porcentagem, $total ) 
{
	$montante = ( $porcentagem / 100 ) * $total;
	return $montante + $total;
}
//consulta se produto ja existe antes de salvar
	$idproduto = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'linkstoreps');
	if($idproduto)
	{	
	    foreach($idproduto as $idp)
	    {
	        if($idp['id_product'])
	        {
				if($idp['CANTIDAD'] > 0)
					$qtdlink = $idp['CANTIDAD'];
				else
					$qtdlink = 0;
	            $upprice = porcentagem_xn(Configuration::get('LINKSTOREPS_TAX_COMISION'),$idp['PRECIOD4']);
	                Db::getInstance()->update('product',array(
															 'quantity' => $qtdlink,
															 'wholesale_price' => $idp['PRECIOD4'],
															 'price' => $upprice,
															 'id_tax_rules_group' => Configuration::get('LINKSTORE_TAX_RULE_GROUP')), 
															'`id_product` = '.$idp['id_product']
															);
                    Db::getInstance()->update('product_shop',array(
															 'wholesale_price' => $idp['PRECIOD4'],
															 'price' => $upprice,
															 'id_tax_rules_group' => Configuration::get('LINKSTORE_TAX_RULE_GROUP')), 
															'`id_product` = '.$idp['id_product']
															);
					$stockid = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'stock WHERE `id_product` = '.$idp['id_product']);										
					
					foreach($stockid as $stk){
						
						Db::getInstance()->update('stock',array(
															'physical_quantity' => $idp['CANTIDAD'], 
															'usable_quantity' => $idp['CANTIDAD']), 
															'`id_stock` = '.$stk['id_stock']);														
					}
					
                    Db::getInstance()->update('stock_available',array(
															'physical_quantity' => $qtdlink, 
															'quantity' => $qtdlink,
															'reserved_quantity' => 0), 
															'`id_product` = '.$idp['id_product']);														
															
				    echo "<pre>"; echo "Producto ID: " . $idp['id_product'] . " Producto actualizado con éxito"; echo "</pre>";
	        } else {
	             echo "<pre>"; echo "No se ha sincronizado ningún producto en Prestashop"; echo "</pre>";
	        }
	    }
	} else {
	     echo "<pre>"; echo "No se ha sincronizado ningún producto en Prestashop";  echo "</pre>";
	}
?>
<head>
    <meta charset="utf-8">

		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<link rel="icon" type="image/x-icon" href="/store/img/favicon.ico" />
		<link rel="apple-touch-icon" href="/store/img/app_icon.png" />

		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="robots" content="NOFOLLOW, NOINDEX">
		<title>
			store  (PrestaShop&trade;)
		</title>
					<link href="/store/admin123/themes/default/public/theme.css" rel="stylesheet" type="text/css" media="all" />
					<link href="/store/admin123/themes/default/css/overrides.css" rel="stylesheet" type="text/css" media="all" />
							<script type="text/javascript" src="/store/js/jquery/jquery-1.11.0.min.js"></script>
					<script type="text/javascript" src="/store/js/jquery/jquery-migrate-1.2.1.min.js"></script>
					<script type="text/javascript" src="/store/js/jquery/plugins/jquery.validate.js"></script>
					<script type="text/javascript" src="/store/js/vendor/spin.js"></script>
					<script type="text/javascript" src="/store/js/vendor/ladda.js"></script>
				<script type="text/javascript" src="../js/admin/login.js?v=1.7.4.4"></script>
<style>
body {
    background-image: url("../../img/linkstore.jpg");
    background-repeat: repeat-x;
}
</style>
<script>

function popup(mylink, windowname)
{
if (! window.focus)return true;
var href;
if (typeof(mylink) == 'string')
   href=mylink;
else
   href=mylink.href;
window.open(href, windowname, 'width=400,height=200,scrollbars=yes');
return false;
}
//-->
setTimeout(function(){
    self.close();
},1);
</script>
</head>