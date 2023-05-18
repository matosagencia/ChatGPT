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
		//update manufacturer
		$first_stepx = explode( '<div id="content">' , $content );
		
		foreach($first_stepx as $fsp){
			$second_stepx = explode('<select',$fsp);
			
			foreach($second_stepx as $scstx){
				$thirty_stepx = explode('<option' , $scstx);
				$tcstxs = str_replace("</option>","",$thirty_stepx);	
				$codigox = str_replace('value="','',$tcstxs);
					
				foreach ($codigox as $cx) {
					$codigoxs = explode('">',$cx);
					$idacode = str_replace(' ','',$codigoxs[0]);
					if(is_numeric($idacode)){
						//$name_manufacturer = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'manufacturer WHERE name LIKE "'.pSQL($codigoxs[1]).'%"');
						//echo "<pre>";
						//	print_r($name_manufacturer[0]['id_manufacturer'] .'--'. $name_manufacturer[0]['IDa']);
						// cria os fabricantes
						$id_manufacturer = Db::getInstance()->getValue('SELECT id_manufacturer FROM '._DB_PREFIX_.'manufacturer WHERE IDa = "'.pSQL($idacode).'"');
						if(empty($id_manufacturer)){
								$sqli = array(
								'id_manufacturer' =>NULL,
								'name' => str_replace("'","",strip_tags(str_replace("Resultados Encontrados 3953", "", $codigoxs[1]))),
								'date_add' => $today,
								'date_upd' => $today,
								'active' => 1,
								'IDa' => $idacode
								);
								if(is_numeric($idacode))
								$xecute = Db::getInstance()->Insert('manufacturer',$sqli);
							} else {
								Db::getInstance()->update('manufacturer',array('IDa' => $idacode), '`id_manufacturer` = '.$id_manufacturer);	
								//consulta tabela lang
								$manulang = Db::getInstance()->getValue('SELECT id_manufacturer FROM '._DB_PREFIX_.'manufacturer_lang WHERE id_manufacturer = "'.pSQL($id_manufacturer).'"');
								if(empty($manulang)){
									$sqli = array(
											'id_manufacturer' => $id_manufacturer,
											'id_lang' => Configuration::get('PS_LANG_DEFAULT'),
											'meta_title' => str_replace("'","",strip_tags(str_replace("Resultados Encontrados 3953", "", $codigoxs[1]))),
									);
									Db::getInstance()->Insert('manufacturer_lang',$sqli);
										$sqli2 = array(
											'id_manufacturer' => $id_manufacturer,
											'id_shop' => 1,
									);
									Db::getInstance()->Insert('manufacturer_shop',$sqli2);
								}
							
							}
						}
					}
			}
			$first_step = explode( '<table' , $content );
		}
		
		foreach($first_step as $fsp){
			$second_step = explode('<tr class="' , $fsp );
			
			foreach($second_step as $scst){
				$thirty_step = explode('<td>' , $scst);
				$thirty_step = str_replace("</td>","",$thirty_step);
				
				$codigo = str_replace(".","",$thirty_step[1]);
				
				//salvar no banco de dados
				//lista produtos da tabela
				$ida = Db::getInstance()->getValue('SELECT IDa FROM '._DB_PREFIX_.'linkstoreps WHERE IDa ="'.pSQL($codigo).'"');
				if(!$ida)
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
					$updatecomission = Db::getInstance()->update('linkstoreps',array(
					'CANTIDAD' => $qdde,
					'PRECIOD4' => trim($thirty_step[8]),
					'update' => $today,
					'PRECIOLISTA' => trim($thirty_step[7])), 
					'`IDa` = '.trim($codigo));
				}

				// echo "<pre>";	print_r($thirty_step);	echo "</pre>"; 

			}
		} //  fim do foreach
		//DISALBE STOCK AND PRODUCTS OBSOLETS
		$linkstoreps = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'linkstoreps` WHERE `update` < "'.$today.'"');
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
//echo "<pre>";	print_r($linkstoreps);	echo "</pre>";
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
?>
<head>
	<style>
		body {
		background-image: url("../img/logo.png");
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