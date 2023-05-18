<?php
	require ('../../../config/config.inc.php');
	 
	$man = array(
	"480" => "000PORDEFECTO",
	"7" => "3M",
	"21" => "AIR802",
	"22" => "AIRLIVE",
	"451" => "ALFA",
	"508" => "ALTELIX",
	"39" => "APC",
	"42" => "APPLE",
	"472" => "ARC WIRELESS",
	"50" => "ATCOM",
	"471" => "AUDIOCODES",
	"60" => "BELKIN",
	"511" => "BPI BANANA PI",
	"482" => "BRAND-REX",
	"74" => "BROTHER",
	"75" => "BTICINO",
	"486" => "BYTEBROTHERS",
	"502" => "CAMBIUM",
	"474" => "CHIPLED",
	"95" => "CISCO",
	"96" => "CITO",
	"112" => "D-LINK",
	"506" => "DAHUA",
	"115" => "DBII",
	"118" => "DIGIUM",
	"128" => "DRAYTEK",
	"133" => "DYMO",
	"135" => "ENGENIUS",
	"491" => "FIBRA",
	"145" => "FLUKE",
	"148" => "FORZA",
	"154" => "FUJITEL",
	"487" => "FURUKAWA",
	"488" => "GARRISON",
	"165" => "GENERICO",
	"169" => "GRANDSTREAM",
	"181" => "HP",
	"509" => "IGNITENET",
	"193" => "IOGEAR",
	"446" => "KALOP",
	"208" => "L-COM",
	"216" => "LEGRAND",
	"222" => "LIFELED",
	"453" => "LIGOWAVE",
	"441" => "LINKCHIP",
	"223" => "LINKMADE",
	"225" => "LINKSYS",
	"467" => "MACROTEL",
	"452" => "MEANWELL",
	"263" => "MIKROTIK",
	"271" => "MOTOROLA",
	"277" => "NCOMPUTING",
	"279" => "NETGEAR",
	"493" => "NETONIX",
	"281" => "NEXXT",
	"294" => "OPTRAL",
	"1" => "OTRAS MARCAS",
	"298" => "OVISLINK",
	"313" => "PLANET",
	"325" => "QLT",
	"510" => "RADIOWAVES",
	"499" => "RADWIN",
	"513" => "REMA",
	"335" => "RF-ELEMENTS",
	"484" => "RFARMOR",
	"410" => "SIL",
	"362" => "SMC",
	"380" => "TABLEPLAST",
	"496" => "TELTONIKA",
	"489" => "TIBOX",
	"392" => "TP-LINK",
	"400" => "TRIPPLITE",
	"402" => "TYCONPOWER",
	"403" => "UBIQUITI",
	"436" => "YXWIRELESS",
	"505" => "ZK TECO",
	"439" => "ZOLODA"
	);
	$today = date("Y-m-d 00:00:00");
	
	//Db::getInstance()->delete('linkstoreps'); die();
	foreach($man as $key => $value){
		// cria os fabricantes
		$id_manufacturer = Db::getInstance()->getValue('SELECT id_manufacturer FROM '._DB_PREFIX_.'manufacturer WHERE name="'.pSQL($value).'"');
		if(!$id_manufacturer)
		{
			$sqli = array(
			'id_manufacturer' =>NULL,
			'name' => str_replace("'","",$value),
			'date_add' => $today,
			'date_upd' => $today,
			'active' => 1
			);
			$xecute = Db::getInstance()->Insert('manufacturer',$sqli);
			$row1 = Db::getInstance()->Insert_ID($xecute); 
			$Product->id_manufacturer = $row1;
		}
	}
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
	    
		$first_step = explode( '<table' , $content );
		
		foreach($first_step as $fsp){
		    
			$second_step = explode('<tr class="' , $fsp );
            
			foreach($second_step as $scst){
           
				$thirty_step = explode('<td>' , $scst);
                
				$thirty_step = str_replace("</td>","",$thirty_step);
			 
					$image = explode('<a href=' , $thirty_step[12]);		
					$sql = array();
					if($thirty_step[1])
						$sql['IDa'] = pSQL(trim($thirty_step[1]));
					if($thirty_step[2])
						$sql['CODIGO'] = pSQL(trim($thirty_step[2]));
					if($thirty_step[3])
						$sql['DESCRIPCION'] = pSQL(trim($thirty_step[3]));
					if($thirty_step[4])
						$sql['CANTIDAD'] = pSQL(trim($thirty_step[4]));
					if($thirty_step[5])
						$sql['TRANSITO'] = pSQL(trim($thirty_step[5]));
					if($thirty_step[6])
						$sql['FECHALLEGADA'] = pSQL(trim($thirty_step[6]));
					if($thirty_step[7])
						$sql['PRECIOLISTA'] = pSQL(trim($thirty_step[7]));
					if($thirty_step[8])
						$sql['PRECIOD4'] = pSQL(trim($thirty_step[8]));
					if($thirty_step[9])
						$sql['CAT'] = pSQL(trim($thirty_step[9]));
					if($thirty_step[10])
						$sql['SUBCAT'] = pSQL(trim($thirty_step[10]));
					if($thirty_step[11])
						$sql['PESO'] = pSQL(trim($thirty_step[11]));
					if($thirty_step[12])
						$sql['IMAGEN'] = pSQL(trim($thirty_step[12]));
					if($thirty_step[13])
						$sql['DESCRIPCION2'] = html_entity_decode(str_replace('<textarea>','',str_replace('</textarea>','',str_replace("'","",$thirty_step[13]))));
					if($thirty_step[14])
						$sql['URLFAB'] = pSQL(trim($thirty_step[14]));
					if($thirty_step[15])
						$sql['FTECNICA'] = pSQL(trim($thirty_step[15]));
					if($thirty_step[16])
						$sql['PREQUERIDO'] = pSQL(trim($thirty_step[16]));
					if($thirty_step[17])
						$sql['PSUGERIDO'] = pSQL(str_replace("</tr>","",$thirty_step[17]));
					if($thirty_step[0])
						$sql['IDMANUFACTURE'] = str_replace('"','',str_replace('>','',$thirty_step[0]));
					$sql['update'] = $today;
				                        
				$ida = Db::getInstance()->getValue('SELECT IDa FROM '._DB_PREFIX_.'linkstoreps WHERE IDa ="'.pSQL($thirty_step[1]).'"');
				if(empty($ida))
				{	
				    if(!empty($sql['CODIGO'])){
				        Db::getInstance()->Insert('linkstoreps',$sql);
					$contanuevos++;
					$listanova .= "<p>Datos del producto:" . $sql['CODIGO'] ." - ".$sql['DESCRIPCION']."</p>";
				    }
					//Db::getInstance()->delete('linkstoreps','where IDa =0');
				} else {
					$updatecomission = Db::getInstance()->update('linkstoreps',array(
					'CANTIDAD' => trim($thirty_step[4]),
					'PRECIOD4' => trim($thirty_step[8]),
					'update' => $today,
					'PRECIOLISTA' => trim($thirty_step[7])), 
					'`IDa` = '.trim($thirty_step[1]));
				}
				
				// echo "<pre>";	print_r($thirty_step);	echo "</pre>"; 
				
			}
		} //  fim do foreach
		
		$linkstoreps = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'linkstoreps` WHERE update <='.$today);
		$contavelhos = 0;
			foreach($linkstoreps as $idpro){
					$contavelhos++;
					$listavelha .= "<p>Datos del producto sem stock:" . $idpro['CODIGO'] ." - ".$idpro['DESCRIPCION']."</p>";
					if(!empty($idpro['id_product']))
						Db::getInstance()->update('product',array('active' => 0), '`id_product` = '.$idpro['id_product']);	
			}
		
	}
	curl_close($ch);
	
	// envia o relatorio por email
	if($contanuevos > 0){
	Mail::Send(
            Configuration::get('PS_LANG_DEFAULT'),
            'listnews',
            Mail::l('Nuevos produtos!'),
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
		},1000000000);
	</script>
</head>		