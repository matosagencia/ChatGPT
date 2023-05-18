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
							$name_manufacturer = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'manufacturer WHERE name LIKE "'.pSQL($codigoxs[1]).'%"');
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