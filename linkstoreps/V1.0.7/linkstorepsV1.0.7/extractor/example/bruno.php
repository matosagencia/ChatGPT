<?php 
require ('/var/www/html/config/config.inc.php');
$today = date("Y-m-d 00:00:00");
//limpar caracteres
function clean($string) {
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
   $string = str_replace('----------','',$string);
   $string = str_replace('-',' ',$string);
   return preg_replace('/[^A-Za-z0-9\-]/', ' ', $string); // Removes special chars.
}

//funcao de imagens
function copyImg($id_entity, $id_image = null, $url, $entity = 'products', $regenerate = true)
{
	$tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
	$watermark_types = explode(',', Configuration::get('WATERMARK_TYPES'));

	switch ($entity)
	{
		default:
		case 'products':
			$image_obj = new Image($id_image);
			$path = $image_obj->getPathForCreation();
		break;
		case 'categories':
			$path = _PS_CAT_IMG_DIR_.(int)$id_entity;
		break;
		case 'manufacturers':
			$path = _PS_MANU_IMG_DIR_.(int)$id_entity;
		break;
		case 'suppliers':
			$path = _PS_SUPP_IMG_DIR_.(int)$id_entity;
		break;
	}
	$url = str_replace(' ', '%20', trim($url));

	// Evaluate the memory required to resize the image: if it's too much, you can't resize it.
	if (!ImageManager::checkImageMemoryLimit($url))
		return false;

	// 'file_exists' doesn't work on distant file, and getimagesize make the import slower.
	// Just hide the warning, the traitment will be the same.
	
	if (Tools::copy($url, $tmpfile))
	{

		ImageManager::resize($tmpfile, $path.'.jpg');
		$images_types = ImageType::getImagesTypes($entity);

		if ($regenerate)
			foreach ($images_types as $image_type)
			{
				ImageManager::resize($tmpfile, $path.'-'.stripslashes($image_type['name']).'.jpg', $image_type['width'], $image_type['height']);
				if (in_array($image_type['id_image_type'], $watermark_types))
					Hook::exec('actionWatermark', array('id_image' => $id_image, 'id_product' => $id_entity));
			}
	}
	else
	{
		unlink($tmpfile);
		return false;
	} 
	unlink($tmpfile);
	return true;
}

$url = 'http://18.210.71.16/oldsite/shopping_dep_12_sec_19.html';
$content = file_get_contents($url); 
$first_step = explode( '<div id="ListItems">' , $content );
//print_r($first_step);
$second_step = explode('</div>' , $first_step[1] );
//print_r($second_step);
$thirty_step = explode('<td width="33%" align="center">' , $first_step[1]);


foreach($thirty_step as $t_step){
	$takeimg = explode('img src="' , $t_step);
	if($takeimg[1])
	{
		$src1 = explode('"',str_replace("images","http://18.210.71.16/oldsite/images",$takeimg[1]));
		$src2 = str_replace("http://artesanato.net:80/","",$src1);
		$takeimp = explode('input' , $t_step);
		$name = explode('<a href="/">',$takeimp[0]);
		if(isset($name[2]))
		{
			$namep = explode('R$',$name[2]);
			$namep1 = explode('<div class="ItemTags">',$namep[0]);
			$namep2 = explode('</a>',$namep[0]);
			$iso8859100 = $namep1[0]; // file must be ISO-8859-1 encoded
			$utf8_10 = utf8_encode($iso8859100);
			$utf8_20 = iconv('ISO-8859-1', 'UTF-8', $iso8859100);
			$utf8_20 = mb_convert_encoding($iso8859100, 'UTF-8', 'ISO-8859-1');
		
			
			$ref =str_replace('type="hidden" name="it" value="','',str_replace('"/>','',str_replace('"<','',$takeimp[1])));
			$preco = explode('"/>',str_replace('type="hidden" name="price" value="','',$takeimp[3]));
			/***********************/
			$Product = new Product();
			$Product->id_product = '';	// set product ID or leave for auto
			$Product->quantity = 100;
			$Product->minimal_quantity = 1;
			$Product->id_tax_rules_group = 0;										   
			$Product->date_add = $today;
			$Product->date_upd = $today;
			if($ref)
				$Product->reference = str_replace('<','',str_replace(' ','',$ref));	
			$Product->id_category_default = 759;		
			$defaultLanguage = new Language();  
			$languages = Language::getLanguages();	
			$validate = new Validate();
			foreach ($languages as $language) 
			{
				$nomeproduto = str_replace("ã","a", html_entity_decode($namep2[0]));
				highlight_string($nomeproduto);
				$titleofproduct = clean($namep2[0]);

				$Product->name[$language['id_lang']] = strip_tags(str_replace(";","",str_replace("(","",str_replace(")","",$nomeproduto))));
				
				//print_r($Product->name[$language['id_lang']]); die();
				$Product->meta_title[$language['id_lang']] = $Product->name[$language['id_lang']];
				$Product->link_rewrite[$language['id_lang']] = str_replace(")","",str_replace("(","",str_replace(" ","",substr($titleofproduct,0,128))));
				$Product->meta_description[$language['id_lang']] = strip_tags(substr($namep1[0],0, 160));
				$Product->meta_keywords[$language['id_lang']] = str_replace(".","",str_replace("  ","",str_replace(")","", str_replace("(","",substr($titleofproduct,0,128)))));
				$Product->description_short[$language['id_lang']] = substr(strip_tags($namep1[0]), 0, 180);
				$Product->description[$language['id_lang']] = htmlspecialchars_decode($namep1[0]);		
			}
				
			$Product->condition = 'new';						
			$Product->wholesale_price = 0;
			$Product->price = str_replace('<','',str_replace(' ','',str_replace(',', '.',$preco[0])));
			if(empty($Product->price))
				$Product->price = 0;
			$Product->id_manufacturer = 14;
			$Product->manufacturer_name = "BOTÕES WE CARE ABOUT.";
				
			//se multiloja
			if (Shop::isFeatureActive())
			{
				Shop::setContext(Shop::CONTEXT_ALL);
			}
			
			$iso88591 = $src2[0]; // file must be ISO-8859-1 encoded
			$utf8_1 = utf8_encode($iso88591);
			$utf8_2 = iconv('ISO-8859-1', 'UTF-8', $iso88591);
			$utf8_2 = mb_convert_encoding($iso88591, 'UTF-8', 'ISO-8859-1');
			$imagem = str_replace("TÃ","T+",$utf8_2);

			$bruno = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'product` WHERE `reference` = '.(int)($Product->reference).' AND `id_category_default` = 759');

			if(!$bruno)
			{
		//		print_r($Product->name);
				$Product->Add();		// returns ID of inserted item or false	
				/**TRATAMENTO DAS IMAGENS DOS ATRIBUTOS**/	
				if($Product->id)
				{
					$cover_at = 1;
					if($imagem)
					{
						$titleofimage = clean($namep1[0]);
						$imagenameat = substr($titleofimage,0,128);
						//compara os asins das imagens
						$imagesTypes = ImageType::getImagesTypes('products');
						$imageNew = new Image();	
						$imageNew->id_product = (int)($Product->id);
						$imageNew->legend = array((int)(Configuration::get('PS_LANG_DEFAULT')) => $imagenameat);
						$imageNew->position = Image::getHighestPosition($cover_at)+1;
						$imageNew->cover = TRUE;				
						if ($imageNew->add()) 
						{
							if (empty($shops))
								$shops = Shop::getContextListShopID();
								$imageNew->associateTo($shops);
								if (!copyImg($Product->id, $imageNew->id, $imagem,'products'))
									Tools::displayError('Error copying image: ');
							}
						}								
					}
			
/*echo "<pre>";
print_r($Product);
print_r($imageNew);
echo "</pre>";*/
				} else {
					echo "cadastrado";
					//die();
				}
			}
	}
}
//$fourty_step = explode('</tr>' , $thirty_step[1]);
//print_r($fourty_step);
?>