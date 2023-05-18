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
	foreach($man as $key => $value){
		// consulta fabricantes
		$manufacturer = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'manufacturer WHERE name like "%'.pSQL($value).'%"');
		//verifica se possui produtos
		foreach($manufacturer as $manufacturerid){
			$idprod = Db::getInstance()->getValue('SELECT id_product FROM '._DB_PREFIX_.'product WHERE id_manufacturer = "'.pSQL($manufacturerid['id_manufacturer']).'"');
			
//			echo "<pre>"; print_r('SELECT id_product FROM '._DB_PREFIX_.'product WHERE id_manufacturer ="'.pSQL($manufacturerid['id_manufacturer']).'"');	 echo "</pre>";
			if(empty($idprod)){
				if($manufacturerid['IDa'] != $key){
					echo "<pre>"; print( $key .'='.$manufacturerid['IDa'].'  -  '. $value .' - '.$idprod); echo "</pre>";
					// deletar registro
					Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'manufacturer WHERE id_manufacturer ="'.pSQL($manufacturerid['id_manufacturer']).'"');
					Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'manufacturer_lang WHERE id_manufacturer ="'.pSQL($manufacturerid['id_manufacturer']).'"');
					Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'manufacturer_shop WHERE id_manufacturer ="'.pSQL($manufacturerid['id_manufacturer']).'"');					
					// deletar registro
					
				}
			}
		}
	}