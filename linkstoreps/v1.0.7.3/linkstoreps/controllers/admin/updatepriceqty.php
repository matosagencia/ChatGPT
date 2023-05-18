<?php

include '../../../../config/config.inc.php';

$Product = new Product();

if(isset($_GET['IDa'])){
	//lista produtos da tabela
	$lsida = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'linkstoreps WHERE overstock = 0 AND IDa ='.$_GET['IDa']);

} else {
	$lsida = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'linkstoreps WHERE overstock = 0 AND id_product IS NOT NULL');
}
foreach($lsida as $ida)
{
	$stockid = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'stock WHERE `id_product` = '.$ida['id_product']);
					
	foreach($stockid as $stk)
	{
		Db::getInstance()->update('stock',array('physical_quantity' => $ida['CANTIDAD'], 'usable_quantity' => $ida['CANTIDAD']), '`id_stock` = '.$stk['id_stock']);
	}
					
	if(Configuration::get('IMPORT_PRICE_LINKSTORE'))
	{
		if(!empty($ida['commision']))
			$price =  porcentagem_xn ( $ida['commision'], $ida['PRECIOD4']);
		else
			$price =  porcentagem_xn ( Configuration::get('IMPORT_PRICE_LINKSTORE'), $ida['PRECIOD4']);
	} else {
		$price =  $ida['PRECIOLISTA'];
	}
					
	$wholesale_price = $ida['PRECIOD4'];
					
	Db::getInstance()->update('product',array('quantity' => $ida['CANTIDAD'], 'wholesale_price' => $wholesale_price,'price' => $price,'active' => 1),'`id_product` = '.trim($ida['id_product']));
	
	Db::getInstance()->update('product_shop',array('wholesale_price' => $wholesale_price,'price' => $price,'active' => 1), '`id_product` = '.trim($ida['id_product']));
	
	Db::getInstance()->update('stock_available',array('physical_quantity' => $ida['CANTIDAD'], 'quantity' => $ida['CANTIDAD']), '`id_product` = '.$ida['id_product']);	
}

//DISALBE STOCK AND PRODUCTS OBSOLETS
$today = date("Y-m-d"); // data atual
$threshold_date = date("Y-m-d", strtotime("-30 days")); // data de referência (30 dias atrás)

// Consulta SQL para selecionar todas as linhas desatualizadas há mais de 30 dias
$linkstoreps = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'linkstoreps` WHERE `update` < "'.$threshold_date.'" AND `overstock` = 0');
$contavelhos = 0;
foreach($linkstoreps as $idpro)
{
	$contavelhos++;
	$listavelha .= "<p>Datos del producto sem stock:" . $idpro['CODIGO'] ." - ".$idpro['DESCRIPCION']."</p>";
	if(!empty($idpro['id_product']))
	{
		$stockid = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'stock WHERE `id_product` = '.$idpro['id_product']);
		foreach($stockid as $stk)
		{
			Db::getInstance()->update('stock',array('physical_quantity' => 0,'usable_quantity' => 0), '`id_stock` = '.$stk['id_stock']);
		}
					
		Db::getInstance()->update('linkstoreps',array('CANTIDAD' => 0), '`IDa` = '.$idpro['IDa']);	
		Db::getInstance()->update('product',array('quantity' => 0), '`id_product` = '.$idpro['id_product']);	
		Db::getInstance()->update('stock_available',array('physical_quantity' => 0, 'quantity' => 0), '`id_product` = '.$idpro['id_product']);	
	}
}
	
if($contavelhos > 0)
{
	Mail::Send(Configuration::get('PS_LANG_DEFAULT'),'listolds',Mail::l('Produtos descontinuados!'),array('{velhos}' => $listavelha),Configuration::get('PS_SHOP_EMAIL'),Configuration::get('PS_SHOP_NAME'));
}
	
// Função de porcentagem: Quanto é X% de N?
function porcentagem_xn ( $porcentagem, $total ) 
{
	$montante = ( $porcentagem / 100 ) * $total;
	return $montante + $total;
}

header('Location: '.$_GET['adminlink'].'&token='.$_GET['token']);
?>