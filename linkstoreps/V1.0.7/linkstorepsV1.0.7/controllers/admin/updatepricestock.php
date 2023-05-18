<?php

	require ('../../../config/config.inc.php');

	$today = date("Y-m-d 00:00:00");
	//login form action url
	$Username = Configuration::get('TECNOGLOBAL_ACCOUNT_USER');
	$Password = Configuration::get('TECNOGLOBAL_ACCOUNT_PASSWORD');
	$Product = new Product();	
	
	//print_r($api_url.'getcatalog?ApiKey='.$apikey.'&utcTimeStamp='.$time.'&signature='.$signature.'&locale=es'); die(); 
	function connect($User,$Pass){
		$service_url = 'http://200.6.78.34/stock/v1/price/';
		$curl = curl_init($service_url);
				curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($curl, CURLOPT_USERPWD, $User.":".$Pass); //Your credentials goes here
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //IMP if the url has https and you don't want to verify source certificate
		$curl_response = curl_exec($curl);
		$response = json_decode($curl_response);
		curl_close($curl);
		return $response;
	}
	
	//getproduct
	$getcatalogdata = connect($Username,$Password);
	
	if(empty($getcatalogdata->products)){
		//echo "verifique seus dados de acesso!";
		
		$extracode = '&gerarpdf11&message='.$getcatalogdata->message;
	} else {
		$extracode = '&gerarpdf11&message=¡Productos importados con éxito!';
		foreach( $getcatalogdata->products as $getcataloga)
		{
			if(!empty($getcataloga->codigoTg))
			{
				//echo "<pre>"; print_r($getcataloga); echo "</pre>"; 
				$tecno = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'tecnoglobal WHERE codigoTg = "'.pSQL($getcataloga->codigoTg).'"');
				
					//trata o preço
					$convertion_rate = Configuration::get('INTCOMEX_CONVERTION_RATE'); 
					$precotecnoglobal = $getcataloga->precio;
					//verifica spread de preço
					if($precotecnoglobal > 0)
						$listadez .= dezpercent($precotecnoglobal,$tecno[0]['id_tecnoglobal']);

					$preco = $precotecnoglobal / $convertion_rate;
					if(Configuration::get('TECNOGLOBAL_PRICE'))
						$precos =  porcentagem_xn ( Configuration::get('TECNOGLOBAL_PRICE'), $preco );
					else 
						$precos =  $preco;
					$pprice = number_format($precos, 6, '.', '');

					if(!empty($tecno[0]['id_product']))
					{
						Db::getInstance()->update('product',array(
								'wholesale_price' => $preco,	
								'quantity' => $getcataloga->stockDisp,
								'price' => $pprice), 
								'`id_product` = '.trim($tecno[0]['id_product'])); 
						Db::getInstance()->update('product_shop',array(
								'wholesale_price' => $preco,
								'price' => $pprice), 
								'`id_product` = '.trim($tecno[0]['id_product']));
						//trata estoque
						$stockid = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'stock WHERE `id_product` = '.$tecno[0]['id_product']);
						foreach($stockid as $stk) 
						{
							
								Db::getInstance()->update('stock',array(
														'physical_quantity' => $getcataloga->stockDisp, 
														'usable_quantity' => $getcataloga->stockDisp), 
														'`id_stock` = '.$stk['id_stock']);														
						}	
						Db::getInstance()->update('stock_available',array(
														'physical_quantity' => $getcataloga->stockDisp, 
														'quantity' => $getcataloga->stockDisp,
														'reserved_quantity' => 0), 
														'`id_product` = '.$tecno[0]['id_product']);	
						$listavelha .= "<p>Stock atualizado:" . $tecno[0]['pnFabricante'] ." |Units:".$estoque."</p>";														
						
						if(!empty($tecno[0]['codigoTg']))
							$listavelha .= "<p>Precio atualizado:" . $tecno[0]['pnFabricante'] ." |CLP:".$pprice."</p>"; 
					}	
					Db::getInstance()->update('tecnoglobal',array(
														'stockDisp' => $getcataloga->stockDisp, 
														'precio' => $getcataloga->precio), '`id_tecnoglobal` = '.trim($tecno[0]['id_tecnoglobal']));
								
			}
		}
	}
		// envia o relatorio por email
		if(!empty($listanova)){
			$template_vars = array(
				'{nuevos}' => $listanova
			);
			
			Mail::Send(
				Configuration::get('PS_LANG_DEFAULT'),
				'tecnoglobalnew',
				Mail::l('Nuevos productos!'),
				$template_vars,
				Configuration::get('PS_SHOP_EMAIL'),
				Configuration::get('PS_SHOP_NAME'),
				null,
				null,
				null,
				null,
				_PS_MODULE_DIR_.'tecnoglobal/mails/',
				false,
				1
			);
		}		
	
	//função para verificar a diferenca de preço
	function dezpercent($pric,$id){
		$intdata = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'tecnoglobal WHERE id_tecnoglobal = '.$id);
		$sprd = $pric*100;
		$sprd1 = 100 - $sprd / $intdata[0]['precio'];
		if($sprd1 != 0)
			Db::getInstance()->update('tecnoglobal',array('historic' => $pric.'|'.$intdata[0]['precio'].'|'.number_format($sprd1, 2, '.', '')),'`id_tecnoglobal` = '.trim($id));
		
		return true;
	}
	
	// envia o relatorio por email
	if(!empty($listadez)){

		$template_vars1 = array(
			'{velhos}' => $listadez
		);
		Mail::Send(
		Configuration::get('PS_LANG_DEFAULT'),
				'tecnoglobalold',
				Mail::l('Productos con precios superiores al 10%!'),
				$template_vars1,
				Configuration::get('PS_SHOP_EMAIL'),
				Configuration::get('PS_SHOP_NAME'),
				null,
				null,
				null,
				null,
				_PS_MODULE_DIR_.'tecnoglobal/mails/',
				false,
				1
			);
	}
	
// envia o relatorio por email
		if(!empty($listavelha)){
			$template_vars = array(
				'{velhos}' => $listavelha
			);
			
			Mail::Send(
				Configuration::get('PS_LANG_DEFAULT'),
				'tecnoglobalold',
				Mail::l('Aviso de Produtos Tecnoglobal com estoque y precio atualizado!'),
				$template_vars,
				Configuration::get('PS_SHOP_EMAIL'),
				Configuration::get('PS_SHOP_NAME'),
				null,
				null,
				null,
				null,
				_PS_MODULE_DIR_.'tecnoglobal/mails/',
				false,
				1
			);
		}
		
		
	function porcentagem_xn ( $porcentagem, $total ) 
	{
		$montante = ( $porcentagem / 100 ) * $total;
		return $montante + $total;
	}
	
	//DISALBE STOCK AND PRODUCTS OBSOLETS
		$tecnoglobalps = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'tecnoglobal` WHERE `update` < "'.$today.'"');
		$contavelhos = 0;
			foreach($tecnoglobalps as $idpro){
				$contavelhos++;
				$listavelha2 .= "<p>SKU: " . $idpro['codigoTg'] ." - ".$idpro['descripcion']."| Last Update: ".$idpro['update']."</p>";
				if(!empty($idpro['id_product'])){
					
					$stockid = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'stock WHERE `id_product` = '.$idpro['id_product']);
					foreach($stockid as $stk){
						
						Db::getInstance()->update('stock',array(
															'physical_quantity' => 0, 
															'usable_quantity' => 0), 
															'`id_stock` = '.$stk['id_stock']);														
					}
					
					Db::getInstance()->update('tecnoglobal',array('stockDisp' => 0), '`id_tecnoglobal` = '.$idpro['id_tecnoglobal']);	
					Db::getInstance()->update('product',array('quantity' => 0), '`id_product` = '.$idpro['id_product']);	
					Db::getInstance()->update('stock_available',array('physical_quantity' => 0, 'quantity' => 0), '`id_product` = '.$idpro['id_product']);	
				}

			}
			//print_r($listavelha2);
	if($contavelhos > 0){
	Mail::Send(
            Configuration::get('PS_LANG_DEFAULT'),
            'tecnoglobalold2',
            Mail::l('Produtos descontinuados!'),
            array(
                '{obsoletos}' => $listavelha2
                ),
				Configuration::get('PS_SHOP_EMAIL'),
				Configuration::get('PS_SHOP_NAME')
        );
	}
	header('Location: '.$_GET['adminlink'].'&token='.$_GET['token'].$extracode);	
//	header('Location: '.$_GET['adminlink'].'&token='.$_GET['token']);	
?>