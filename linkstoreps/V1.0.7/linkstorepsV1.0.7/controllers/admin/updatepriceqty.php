<?php
	include '../../../../config/config.inc.php';

	$today = date("Y-m-d 00:00:00");


	//login form action url
	//$api_url="https://linkstore.cl/listados/listado_d4/"; 
	$api_url="https://lk.cl/listados/listado_d4/";
	$client_id = Configuration::get('LINKSTOREPS_ACCOUNT_EMAIL');//'distribuidor'
	$client_secret = Configuration::get('LINKSTOREPS_ACCOUNT_PASSWORD');//'5626503100'

	$context = stream_context_create(array(
    'http' => array(
	'header' => "Authorization: Basic " . base64_encode("$client_id:$client_secret"),
    ),
	));
	$Product = new Product();
	$today = date("Y-m-d 00:00:00");

	//$content = file_get_contents($api_url, false, $context);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $api_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERPWD, "$client_id:$client_secret");
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	$content = curl_exec($ch);
	$info = curl_getinfo($ch);
	    
	$contanuevos = 0;
	//print_r($content); die();
	if(empty($content)){
		echo "verifique seus dados de acesso!";
		//print_r($content);
		
	} else {
		$first_step = explode( '<table' , $content );
		
		foreach($first_step as $fsp){
			$second_step = explode('<tr class="' , $fsp );
			
			foreach($second_step as $scst){
				$thirty_step = explode('<td>' , $scst);
				$thirty_step = str_replace("</td>","",$thirty_step);
				
				$codigo = str_replace(".","",$thirty_step[1]);
				$codigo = str_replace("\n			","",$codigo);
				//salvar no banco de dados
				
				if(!empty($_GET['update'])){
					$ida = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'linkstoreps WHERE IDa ="'.pSQL($_GET['IDa']).'"');
				} else {
					//lista produtos da tabela
					$ida = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'linkstoreps WHERE IDa ="'.pSQL($codigo).'"');
				}
				
				if(empty($ida))
				{

					$image = explode('<a href=' , $thirty_step[12]);
					$sql = array();
					if($codigo)
						$sql['IDa'] = trim($codigo);
					if($thirty_step[2])
						$sql['CODIGO'] = trim(str_replace(".","",$thirty_step[2]));
					if($thirty_step[3])
						$sql['DESCRIPCION'] = trim($thirty_step[3]);
					if($thirty_step[4]){
						if(trim($thirty_step[4]) > 0)
						$sql['CANTIDAD'] = trim($thirty_step[4]);
						else
						$sql['CANTIDAD'] = 0;
					}
					if($thirty_step[5])
						$sql['TRANSITO'] = trim($thirty_step[5]);
					if($thirty_step[6])
						$sql['FECHALLEGADA'] = trim($thirty_step[6]);
					if($thirty_step[7])
						$sql['PRECIOLISTA'] = trim($thirty_step[7]);
					if($thirty_step[8])
						$sql['PRECIOD4'] = trim($thirty_step[8]);
					if($thirty_step[9])
						$sql['CAT'] = trim($thirty_step[9]);
					if($thirty_step[10])
						$sql['SUBCAT'] = trim($thirty_step[10]);
					if($thirty_step[11])
						$sql['PESO'] = trim($thirty_step[11]);
					if($thirty_step[12])
						$sql['IMAGEN'] = trim($thirty_step[12]);
					if($thirty_step[13])
						$sql['DESCRIPCION2'] = html_entity_decode(str_replace('<textarea>','',str_replace('</textarea>','',str_replace("'","",$thirty_step[13]))));
					if($thirty_step[14])
						$sql['URLFAB'] = trim($thirty_step[14]);
					if($thirty_step[15])
						$sql['FTECNICA'] = trim($thirty_step[15]);
					if($thirty_step[16])
						$sql['PREQUERIDO'] = trim($thirty_step[16]);
					if($thirty_step[17])
						$sql['PSUGERIDO'] = trim(str_replace("</tr>","",$thirty_step[17]));
					if($thirty_step[0]){
						$sql['IDMANUFACTURE'] = str_replace('"','',str_replace('>','',$thirty_step[0]));
						}
					$sql['update'] = $today;
					
					
					if($codigo > 0){
						Db::getInstance()->Insert('linkstoreps',$sql);
						$contanuevos++;
						$listanova .= "<p>Datos del producto:" . $sql['CODIGO'] ." - ".$sql['DESCRIPCION']."</p>";
					}
				} else {
					
					if($thirty_step[4]){
						if(trim($thirty_step[4]) > 0)
						$qdde = trim($thirty_step[4]);
						else
						$qdde = 0;
					}
					
					
					
					if($ida[0]['overstock'] == 0) {
						$stockid = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'stock WHERE `id_product` = '.$ida[0]['id_product']);
						
						Db::getInstance()->update('linkstoreps',array(
							'CANTIDAD' => $qdde,
							'PRECIOD4' => trim($thirty_step[8]),
							'update' => $today,
							'PRECIOLISTA' => trim($thirty_step[7])), 
							'`IDa` = '.trim($ida[0]['IDa']));
					
						foreach($stockid as $stk){
							Db::getInstance()->update('stock',array('physical_quantity' => $qdde, 'usable_quantity' => $qdde), '`id_stock` = '.$stk['id_stock']);
						}
					
						if(Configuration::get('IMPORT_PRICE_LINKSTORE')){
							if(!empty($ida[0]['commision']))
								$price =  porcentagem_xn ( $ida[0]['commision'], $thirty_step[8]);
							else
								$price =  porcentagem_xn ( Configuration::get('IMPORT_PRICE_LINKSTORE'), $thirty_step[8]);
						} else {
							$price =  $thirty_step[7];
						}
					
						$wholesale_price = $thirty_step[8];
					
						Db::getInstance()->update('product',array(
							'wholesale_price' => $wholesale_price,
							'price' => $price,
							'active' => 1), 
							'`id_product` = '.trim($ida[0]['id_product']));
						Db::getInstance()->update('product_shop',array(
							'wholesale_price' => $wholesale_price,
							'price' => $price,
							'active' => 1), 
							'`id_product` = '.trim($ida[0]['id_product']));
						Db::getInstance()->update('product',array('quantity' => $qdde), '`id_product` = '.$ida[0]['id_product']);	
						Db::getInstance()->update('stock_available',array('physical_quantity' => $qdde, 'quantity' => $qdde), '`id_product` = '.$ida[0]['id_product']);	
					} else {
						//muda precos apenas
						Db::getInstance()->update('linkstoreps',array(
							'PRECIOD4' => trim($thirty_step[8]),
							'update' => $today,
							'PRECIOLISTA' => trim($thirty_step[7])), 
							'`IDa` = '.trim($ida[0]['IDa']));
										
						if(Configuration::get('IMPORT_PRICE_LINKSTORE')){
							if(!empty($ida[0]['commision']))
								$price =  porcentagem_xn ( $ida[0]['commision'], $thirty_step[8]);
							else
								$price =  porcentagem_xn ( Configuration::get('IMPORT_PRICE_LINKSTORE'), $thirty_step[8]);
						} else {
							$price =  $thirty_step[7];
						}
					
						$wholesale_price = $thirty_step[8];
					
						Db::getInstance()->update('product',array(
							'wholesale_price' => $wholesale_price,
							'price' => $price,
							'active' => 1), 
							'`id_product` = '.trim($ida[0]['id_product']));
						Db::getInstance()->update('product_shop',array(
							'wholesale_price' => $wholesale_price,
							'price' => $price,
							'active' => 1), 
							'`id_product` = '.trim($ida[0]['id_product']));
					}
				}  
			} 
		} //  fim do foreach
		//DISALBE STOCK AND PRODUCTS OBSOLETS
		$linkstoreps = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'linkstoreps` WHERE `update` < "'.$today.'" AND `overstock` = 0');
		$contavelhos = 0;
			foreach($linkstoreps as $idpro){
				$contavelhos++;
				$listavelha .= "<p>Datos del producto sem stock:" . $idpro['CODIGO'] ." - ".$idpro['DESCRIPCION']."</p>";
				if(!empty($idpro['id_product'])){
					
					$stockid = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'stock WHERE `id_product` = '.$idpro['id_product']);
					foreach($stockid as $stk){
						
						Db::getInstance()->update('stock',array(
															'physical_quantity' => 0, 
															'usable_quantity' => 0), 
															'`id_stock` = '.$stk['id_stock']);														
					}
					
					Db::getInstance()->update('linkstoreps',array('CANTIDAD' => 0), '`IDa` = '.$idpro['IDa']);	
					Db::getInstance()->update('product',array('quantity' => 0), '`id_product` = '.$idpro['id_product']);	
					Db::getInstance()->update('stock_available',array('physical_quantity' => 0, 'quantity' => 0), '`id_product` = '.$idpro['id_product']);	
				}
				
			}
	}
	curl_close($ch);
	// envia o relatorio por email
	if($contanuevos > 0){
	Mail::Send(
            Configuration::get('PS_LANG_DEFAULT'),
            'listnews',
            Mail::l('Nuevos productos!'),
            array(
                '{nuevos}' => $listanova
                ),
            Configuration::get('PS_SHOP_EMAIL'),
            Configuration::get('PS_SHOP_NAME')
        );
	}

	if($contavelhos > 0){
	Mail::Send(
            Configuration::get('PS_LANG_DEFAULT'),
            'listolds',
            Mail::l('Produtos descontinuados!'),
            array(
                '{velhos}' => $listavelha
                ),
            Configuration::get('PS_SHOP_EMAIL'),
            Configuration::get('PS_SHOP_NAME')
        );
	}
	
	// Função de porcentagem: Quanto é X% de N?
	function porcentagem_xn ( $porcentagem, $total ) 
	{
		$montante = ( $porcentagem / 100 ) * $total;
		return $montante + $total;
	}
	header('Location: '.$_GET['adminlink'].'&token='.$_GET['token']);
?>