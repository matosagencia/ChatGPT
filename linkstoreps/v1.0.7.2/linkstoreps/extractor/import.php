<?php
	require ('../../../config/config.inc.php');

	$today = date("Y-m-d 00:00:00");


	//login form action url
	$api_url="https://linkstore.cl/listados/listado_d4/"; 

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
				//lista produtos da tabela
				$ida = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'linkstoreps WHERE IDa ="'.pSQL($codigo).'" AND overstock = 0');
				
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
					//tratamento da quantidade
					if($thirty_step[4]){
						if(trim($thirty_step[4]) > 0)
						$qdde = trim($thirty_step[4]);
						else
						$qdde = 0;
					}
					//captura do estoque prestashop
					$stockid = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'stock WHERE `id_product` = '.$ida[0]['id_product']);
					//atualizacao do estoque prestashop
					foreach($stockid as $stk){
						Db::getInstance()->update('stock',array(
															'physical_quantity' => $qdde, 
															'usable_quantity' => $qdde), 
															'`id_stock` = '.$stk['id_stock']);														
					}
					// tratamento do preço
					if(!empty(Configuration::get('IMPORT_PRICE_LINKSTORE')))
					{						if(!empty($ida[0]['commision']))
							$taxcom = $ida[0]['commision'];
						else
						$taxcom = Configuration::get('IMPORT_PRICE_LINKSTORE');
				}
					if($taxcom){
						$price =  porcentagem_xn ( $taxcom, $thirty_step[8]);
					} else {
						$price =  $thirty_step[7];
					}
					
					$wholesale_price = $thirty_step[8];
					//verificacao do historico de alta e baixa do preço
					dezpercent($thirty_step[8],$ida[0]['IDa']);
					//se o preço for inverior ao que se definiu nao atualiza
					if(!empty(Configuration::get('LINKSTORE_STOPLOSS'))){
						$links = Db::getInstance()->getValue('SELECT PRECIOD4 FROM '._DB_PREFIX_.'linkstoreps WHERE `id_product` = '.$ida[0]['id_product']);
						$stoploss = 100 - Configuration::get('LINKSTORE_STOPLOSS');
						$stoplossmenor = ($links / 100) * $stoploss;
					
						if($thirty_step[8] >= $stoplossmenor){
							Db::getInstance()->update('linkstoreps',array(
							'PRECIOD4' => trim($thirty_step[8]),
							'PRECIOLISTA' => trim($thirty_step[7])), 
						'`IDa` = '.trim($ida[0]['IDa']));
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
					} else {
						Db::getInstance()->update('linkstoreps',array(
							'PRECIOD4' => trim($thirty_step[8]),
							'PRECIOLISTA' => trim($thirty_step[7])), 
						'`IDa` = '.trim($ida[0]['IDa']));
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
					
					/* $sprd = $thirty_step[8]*100;
		$sprd1 = 100 - $sprd / $ida[0]['PRECIOD4'];
			if($sprd1 != 0){
						echo "<pre>".$sprd1.'---'; print_r($thirty_step[8] .'|'.$taxcom .'% -'. $price .'-id:'.$ida[0]['IDa']); echo "</pre>"; 
			} */
					Db::getInstance()->update('linkstoreps',array('CANTIDAD' => $qdde,'update' => $today),'`IDa` = '.trim($ida[0]['IDa']));
					Db::getInstance()->update('product',array('quantity' => $qdde), '`id_product` = '.$ida[0]['id_product']);	
					Db::getInstance()->update('stock_available',array('physical_quantity' => $qdde, 'quantity' => $qdde), '`id_product` = '.$ida[0]['id_product']);	
				}
			} 
		} //  fim do foreach
		
		
		//DISALBE STOCK AND PRODUCTS OBSOLETS
		$linkstoreps = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'linkstoreps` WHERE `update` < "'.$today.'" AND overstock = 0');
		$contavelhos = 0;
			foreach($linkstoreps as $idpro){
				$contavelhos++;
				$listavelha .= "<p>SKU: " . $idpro['CODIGO'] ." - ".$idpro['DESCRIPCION']."| Last Update: ".$idpro['update']."</p>";
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
			//print_r($listavelha);
	}
	curl_close($ch);
	
	//função para verificar a diferenca de preço
	function dezpercent($pric,$id){
		$intdata = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'linkstoreps WHERE IDa = '.$id);
		$differ = Configuration::get('IMPORT_PERCENTUAL_DIFFERENCE');
		
		/* if($intdata[0]['PRECIOD4'] > 0){
			$maior = 100 + $differ;
			$menor = 100 - $differ;
					
			$dezmaior = ($intdata[0]['PRECIOD4'] / 100) * $maior;
			$dezmenor = ($intdata[0]['PRECIO D4'] / 100) * $menor;
		 
			if($pric >= $dezmaior){
				$listaz .= 'Item:'. $intdata[0]['CODIGO'] ." es con precio hasta 10% más alto<br>";
				$sprd = 1;
			} else if($pric <= $dezmenor ){
				$listaz .= 'Item:'. $intdata[0]['CODIGO'] ." es con precio hasta 10% abajo<br>";
				$sprd = 2;
			} else {
				$sprd = 0;
			} 
		} else {
			$sprd = 0;
		} */
		
		$sprd = $pric*100;
		$sprd1 = 100 - $sprd / $intdata[0]['PRECIOD4'];
		if($sprd1 != 0)
			Db::getInstance()->update('linkstoreps',array('historic' => $pric.'|'.$intdata[0]['PRECIOD4'].'|'.number_format($sprd1, 2, '.', '')),'`IDa` = '.trim($id));
		if($listaz)
			return $listaz;
		else
			return false;
	}
	// envia o relatorio por email
	if($contanuevos > 0){
	Mail::Send(
            Configuration::get('PS_LANG_DEFAULT'),
            'listnews',
            Mail::l('Nuevos productos!'),
            array(
                '{nuevos}' => $listanova
                ),
				Configuration::get('LINKSTORE_EMAIL'),
            Configuration::get('PS_SHOP_NAME')
        );
	}

	if($contavelhos > 0){
	Mail::Send(
            Configuration::get('PS_LANG_DEFAULT'),
            'listold',
            Mail::l('Produtos descontinuados!'),
            array(
                '{velhos}' => $listavelha
                ),
				Configuration::get('LINKSTORE_EMAIL'),
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