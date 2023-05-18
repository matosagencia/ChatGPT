<?php
	require ('../../../config/config.inc.php');

	$today = date("Y-m-d 00:00:00");


	//login form action url
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

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $api_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERPWD, "$client_id:$client_secret");
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	$content = curl_exec($ch);
	$info = curl_getinfo($ch);
	curl_close($ch);    
	$contanuevos = 0;

	if(empty($content)){
		echo "verifique seus dados de acesso!";
		
	} else {
		$first_step = explode( '<table' , $content );
		
		foreach($first_step as $fsp)
		{
			$second_step = explode('<tr class="' , $fsp );
			
			foreach($second_step as $scst)
			{
				$thirty_step = explode('<td>' , $scst);
				$thirty_step = str_replace("</td>","",$thirty_step);
				
				$codigo = str_replace(".","",$thirty_step[1]);
				$codigo = str_replace("\n			","",$codigo);
				//salvar no banco de dados
				//lista produtos da tabela
				$ida = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'linkstoreps WHERE IDa ="'.pSQL($codigo).'"');
	
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
					if($thirty_step[4])
					{
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
					
					
					if($codigo > 0)
					{
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
					if(!empty(trim($thirty_step[8])) && !empty(trim($thirty_step[7]))) 
					{
						Db::getInstance()->update('linkstoreps',array(
							'CANTIDAD' => $qdde,
							'update' => $today,
							'PRECIOD4' => trim($thirty_step[8]),
							'PRECIOLISTA' => trim($thirty_step[7])),
							'`IDa` = '.trim($ida[0]['IDa']));
					}
				} 
			} //  fim do foreach
		}
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
	
	header('Location: '.$_GET['adminlink'].'&token='.$_GET['token']);
?>