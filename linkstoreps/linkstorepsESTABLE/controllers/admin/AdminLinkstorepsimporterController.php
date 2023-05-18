<?php 
	
	class AdminLinkstorepsimporterController extends ModuleAdminController {
		
		protected $max_file_size = null;
		protected $max_image_size = null;
		
		protected $_category;
		/**
			* @var string name of the tab to display
		*/
		protected $tab_display;
		protected $tab_display_module;
		
		/**
			* The order in the array decides the order in the list of tab. If an element's value is a number, it will be preloaded.
			* The tabs are preloaded from the smallest to the highest number.
			* @var array Product tabs.
		*/
		protected $available_tabs = array();
		
		protected $default_tab = 'Informations';
		
		protected $available_tabs_lang = array();
		
		protected $position_identifier = 'IDa';
		
		protected $submitted_tabs;
		
		public function __construct()
		{
			$this->bootstrap = true;
			$this->context = Context::getContext();
			
			// Define query e campos a serem mostrados -  seleciona nome sobrenome email se ja gerou fatura e nao tem cod rastreio nem data de criacao do rastreio
			$this->className = 'Product';
			$this->list_id = 'linkstoreps';   // nome do campo da funcao Bulk
			$this->lang = false;
			$this->list_no_link = true;
			$this->table = 'linkstoreps';
			$this->identifier = 'IDa';
			$this->addRowAction('gerarpdf');
			
		
			/*        $this->_filter = "AND a.fk_etiq_ender = '0'";							
				$this->_filter = "AND a.invoice_date <> '0000-00-00 00:00:00'"; 
				$this->_filter = "AND a.data_criacao = '0000-00-00 00:00:00'";
				$this->_where = "AND a.shipping_number != ''";											
				$this->_defaultOrderBy = 'id';
				$this->_defaultOrderWay = 'DESC'; 
			*/
			$this->fields_list = array(
            'IDa' => array(
			'title' => 'IDa',
			'align' => 'left',
			'width' => 80,
            ),
            'CODIGO' => array(
			'title' => 'Codigo do Produto',
			'align' => 'left'
            ),
            'DESCRIPCION' => array(
			'title' => 'Descricao',
			'align' => 'left'
            ),			
			'PRECIOLISTA' => array(
			'title' => 'Precio Lista',
			'align' => 'left'
            ),			
            'PRECIOD4' => array(
			'title' => 'PRECIO D4',
			'type'  => 'text',
			'align' => 'left'
            ),
            'CANTIDAD' => array(
			'title' => 'Cantidad',
			'type'  => 'text',
			'align' => 'left'
            ),	
            'id_product' => array(
			'title' => 'Id Prestashop',
			'type'  => 'text',
			'align' => 'left'
            ),	
            'id_category' => array(
			'title' => 'Id Cat',
			'type'  => 'text',
			'align' => 'left'
            ),            
			);
			
			
			$this->bulk_actions = array(
            'importar' => array(
			'text' => 'Importar catalogo',
			'icon' => 'icon-file-o',
            ),
            'atualizar' => array(
			'text' => 'Atualizar dados',
			'icon' => 'icon-file-o',
            ),		
            'limpiar' => array(
			'text' => 'Desvincular productos',
			'icon' => 'icon-file-o',
            ),					
			);
			
			
			parent::__construct();
			
		}
		
		
		public function initContent() {
			
			// Atualizar descricao
			if (Tools::isSubmit('gerarpdf')) {
				if(!empty(Tools::getValue('id_category'))){
				    if(Tools::getValue('id_category') > 2)
					    Db::getInstance()->update('linkstoreps',array('id_category' => Tools::getValue('id_category')), '`IDa` = '.Tools::getValue('IDa'));
				}
				
				$productml = Tools::getValue('IDa');
				/*echo "<pre>1";
					print_r($productml);
					echo "</pre>"; 
				die();*/
				$this->descmercado($productml);
				
				} else {
				// Varios pedidos selecionados
				if ($this->action == 'bulkimportar') {
					
					foreach (Tools::getValue('linkstorepsBox') as $id) 
					{
						/*
							echo "<pre>";
							print_r($id);
							echo "</pre>";
							die();
						*/
						$status = "importar";
						$this->statusmercado($id);
						
					}
				}
				//
				if ($this->action == 'bulkatualizar') {
					
					foreach (Tools::getValue('linkstorepsBox') as $id) 
					{
						/*
							echo "<pre>";
							print_r($id);
							echo "</pre>";
							die();
						*/
						$status = "atualizar";
						$this->descmercado($id);
						
					}
				}
				if ($this->action == 'bulklimpiar') {
					
					foreach (Tools::getValue('linkstorepsBox') as $id) 
					{
						/*
							echo "<pre>";
							print_r($id);
							echo "</pre>";
							die();
						*/
						$status = "limpiar";
						$this->limpiar($id);
					}
				}				
			}
			
			parent::initContent();
		}
		
		public function initPageHeaderToolbar() {
			
			$this->page_header_toolbar_btn['SolicEtiq2'] = array(
            'href' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/linkstoreps/extractor/import.php',
            'desc' => 'Baixar Catalogo Linkstore',
			'target'=> 'blank',
            'icon' => 'process-icon-update'
			);
			$this->page_header_toolbar_btn['SolicEtiq'] = array(
            'href' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/linkstoreps/sync.php',
            'desc' => 'Sincronizar con productos existentes',
			'target'=> 'blank',
            'icon' => 'process-icon-update'
			);		
			
			parent::initPageHeaderToolbar();
		}
		
		public function initToolbar() {
			
			parent::initToolbar();
			
			// Desativa botoes
			unset($this->toolbar_btn['new']);
			
			$this->toolbar_title = 'Sincronizar';
		}
		
		// Funcao alterada para acerto de bug do Prestashop
		public function processResetFilters($list_id = null) {
			parent::processResetFilters();
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminLinkstorepsimporter'));
		}
		
		//funcoes pega imagens
		public function getProductImages($id_product)
		{
			$id_image = Db::getInstance()->ExecuteS('SELECT `id_image` FROM `'._DB_PREFIX_.'image` WHERE `id_product` = '.(int)($id_product));
			return $id_image;
		}
		
		public function getstock($id_product)
		{	
			$stock = Db::getInstance()->ExecuteS('SELECT `quantity` FROM `'._DB_PREFIX_.'product` WHERE `id_product` = '.(int)($id_product));
			
			if(!$checkstock)
			$stock = Db::getInstance()->ExecuteS('SELECT `quantity` FROM `'._DB_PREFIX_.'stock_available` WHERE `id_product` = '.(int)($id_product).' and `id_product_attribute` = 0');
			
			return $stock;
		}
		
		// Função de porcentagem: Quanto é X% de N?
		public function porcentagem_xn ( $porcentagem, $total ) 
		{
			$montante = ( $porcentagem / 100 ) * $total;
			return $montante + $total;
		}
		
		public function tirarAcentos($string)
		{
			return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","a A e E i I o O u U n N"),$string);
		}
		
		public function removeinvalidos($var)
		{
			$tam = 200;
			$sizeName = strlen($var); 
			$a="ÁáÉéÍíÓóÚúÇçÃãÀàÂâÊêÎîÔôÕõÛû& -...@#$%¨&*()_+}=}{[]^~?/:;><,'´`\"";
			$b="AaEeIiOoUuCcAaAaAaEeIiOoOoUue ";
			$var = strtr($var,$a,$b);    
			$var = strtolower($var); 
			$var = str_replace("("," ",$var);
			$var = str_replace(")"," ",$var);		
			$var = str_replace("["," ",$var);		
			$var = str_replace("]"," ",$var);							   
			$var = str_replace("/"," ",$var);							     
			$var = str_replace(":"," ",$var);							     
			$var = str_replace("--"," ",$var);					     
			if ($sizeName>$tam)
			{
				$var = substr($var,0,$tam);
			}  
			return $var;
		}
		
		// cria gerarpdf action list
		public function displayGerarPDFLink($token = null, $id) { 
			
			$cat = Category::getCategories( (int)($this->context->language->id), true, false);
			
			if (!array_key_exists('gerarpdf', self::$cache_lang)) { 
				
				$idd = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'linkstoreps WHERE IDa = '.(int)$id);
				if(!empty($idd)){
					$idcategory_default = Db::getInstance()->getValue('SELECT id_category_default FROM '._DB_PREFIX_.'product WHERE id_product = '.(int)$idd[0]['id_product']);
					$catname = Db::getInstance()->getValue('SELECT name FROM '._DB_PREFIX_.'category_lang WHERE id_category = '.(int)$idcategory_default.' and id_lang = '. $this->context->language->id);
					$this->context->smarty->assign(array('catname' => $catname));
				}
				$idml = array();
				foreach($cat as $idcat){
					//verifica o parent
					$parent = Db::getInstance()->getValue('SELECT name FROM '._DB_PREFIX_.'category_lang WHERE id_category = '.(int)$idcat['id_parent'].' and id_lang = '. $this->context->language->id);
					if($idcat['id_category'] > 2){
						if($idcat['id_category'] != $idd[0]['id_category']){
							$idml[] = '<option value='.self::$currentIndex.'&'.$this->identifier.'='.$id.'&gerarpdf&token='.($token != null ? $token : $this->token).'&id_category='.$idcat['id_category'].' >'.$parent .'|'.$idcat['name'].'</option>';
						 }else {
						    $idml[] = '<option value='.self::$currentIndex.'&'.$this->identifier.'='.$id.'&gerarpdf&token='.($token != null ? $token : $this->token).'&id_category='.$idcat['id_category'].' selected=checked>'.$parent .'|'. $idcat['name'].'</option>';
						 }
					}
				}
				
					$this->context->smarty->assign(array(
				'idd' => $idd,
				'idml' => $idml				
				));
				
			}
			
			return $this->context->smarty->fetch(_PS_MODULE_DIR_.'linkstoreps/views/templates/admin/list_action_gerarcategoria.tpl');
		}
		
		public function statusmercado($id)
		{
			//seleciona os dados do produto 
			$linkstoredata = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'linkstoreps WHERE IDa = "'. $id .'"');
			$prd = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'product WHERE id_product = "'. $linkstoredata[0]['id_product'] .'"');
			if(empty($prd[0]['id_product']))
			{
				
				$Product = new Product();
				$Product->id_product = '';
				//grava dados de texto sobre o produto 
				$defaultLanguage = new Language((int)(Configuration::get('PS_LANG_DEFAULT')));  
				/* Add a new product */
				$languages = Language::getLanguages();	
				foreach ($languages as $language) 
				{
					$Product->name[$language['id_lang']] = trim(substr($linkstoredata[0]['CODIGO'] .' '. $linkstoredata[0]['DESCRIPCION'],0,128));
					$Product->meta_title[$language['id_lang']] = trim(utf8_encode(substr($linkstoredata[0]['CODIGO'] .' '. $linkstoredata[0]['DESCRIPCION'],0,128)));
					$nomelink = trim(urlencode(utf8_encode($this->removeinvalidos($linkstoredata[0]['DESCRIPCION']))));	
					$Product->link_rewrite[$language['id_lang']] = trim(substr(str_replace(".","",str_replace("+","_",str_replace("%","_",$this->removeinvalidos($nomelink)))),0,128));
					//trata a descricao
					$Product->description_short[$language['id_lang']] = trim(substr(strip_tags($linkstoredata[0]['DESCRIPCION']), 0, 180));
					$Product->meta_description[$language['id_lang']] = trim(strip_tags(substr($linkstoredata[0]['DESCRIPCION'],0, 180)));
					$Product->meta_keywords[$language['id_lang']] = trim(substr(urlencode(utf8_encode($linkstoredata[0]['DESCRIPCION'])),0,255));
					$Product->description[$language['id_lang']] = trim($linkstoredata[0]['DESCRIPCION2']);	
				}
				$Product->id_shop_default = Configuration::get('PS_SHOP_DEFAULT');
				if(Configuration::get('IMPORT_PRICE_LINKSTORE'))
				$Product->price =  $this->porcentagem_xn ( Configuration::get('IMPORT_PRICE_LINKSTORE'), $linkstoredata[0]['PRECIOD4'] );
				else 
				$Product->price =  $linkstoredata[0]['PRECIOLISTA'];
				
				$Product->wholesale_price = $linkstoredata[0]['PRECIOD4'];
				$Product->quantity = $linkstoredata[0]['CANTIDAD'];
				$Product->minimal_quantity = 1;
				$Product->date_add = $linkstoredata[0]['FECHALLEGADA'];
				$Product->date_upd = $linkstoredata[0]['FECHALLEGADA'];
				//trata as dimensões
				$Product->width = 0;		
				$Product->height = 0;		
				$Product->depth = 0;		
				$Product->id_tax_rules_group = Configuration::get('LINKSTORE_TAX_RULE_GROUP');
				$Product->weight = $linkstoredata[0]['PESO'];
				$Product->reference = trim($linkstoredata[0]['CODIGO']);	
				$Product->IDa = trim($linkstoredata[0]['IDa']);	
				//seleciona a categoria do produto ?????????????????
				$Product->id_category_default = $linkstoredata[0]['id_category'];
				$Product->product_categories = array($linkstoredata[0]['id_category']);
				//se multiteste
				if (Shop::isFeatureActive())
				{
					Shop::setContext(Shop::CONTEXT_ALL);
				}
				
				//print_r($Product);
				//evita duplicidade			
				//verificamos se tem registro desse produto para esse tipo de listagem ativo
				$Product->Add();
				
				Db::getInstance()->update('linkstoreps',array('id_product' => $Product->id), '`IDa` = '.$id);
				
				//grava as imagens
				if($Product->id)
				{	
				    
				    //atualiza o codigo do mercadolivre e da descricao
					$sqli = array(
								'id_category' => $linkstoredata[0]['id_category'],
								'id_product' => $Product->id,
								'position' => MAX(position)+1
							);
					Db::getInstance()->Insert('category_product',$sqli);
				    
				    preg_match_all('/<a[^>]*?\s+href\s*=\s*"([^"]+)"[^>]*?>/i', $linkstoredata[0]['IMAGEN'], $matches);
					$count = 0;
					foreach($matches[1] as $match)
					{
					    $count++;
					    
						//$imurl = explode("&",$match.PHP_EOL);
						$imurl = $match;
						
						/*Imagem principal**/
						if($count == 1)
						{
							$cover = 1;
							$this->importimg($imurl,$Product->id,$linkstoredata[0]['DESCRIPCION'],$cover);
							}else{
							$cover = 0;
							$this->importimg($imurl,$Product->id,$linkstoredata[0]['DESCRIPCION'],$cover);
						}
						
					}
					
					Db::getInstance()->update('stock_available',array(
					'quantity' => $linkstoredata[0]['CANTIDAD']), 
					'`id_product` = '.$Product->id);		
				}
                
				$this->confirmations[] = "<button data-dismiss='alert' class='close' type='button'>x</button>".$linkstoredata[0]['DESCRIPCION'] ."<br />¡Producto importado con éxito!";		
				
				} else {
				$this->displayWarning('este producto se guardó anteriormente.');
				
				if(Configuration::get('IMPORT_PRICE_LINKSTORE'))
				$price =  $this->porcentagem_xn ( Configuration::get('IMPORT_PRICE_LINKSTORE'), $linkstoredata[0]['PRECIOD4'] );
				else 
				$price =  $linkstoredata[0]['PRECIOLISTA'];
				
				Db::getInstance()->update('product',array(
				'reference' => trim($linkstoredata[0]['CODIGO']),
				'quantity' => $linkstoredata[0]['CANTIDAD'],
				'wholesale_price' => $linkstoredata[0]['PRECIOD4'],
				'price' => $price), 
				'`id_product` = '.$linkstoredata[0]['id_product']);
				Db::getInstance()->update('product_shop',array(
				'wholesale_price' => $linkstoredata[0]['PRECIOD4'],
				'price' => $price), 
				'`id_product` = '.$linkstoredata[0]['id_product']);		
				Db::getInstance()->update('stock_available',array(
				'quantity' => $linkstoredata[0]['CANTIDAD']), 
				'`id_product` = '.$linkstoredata[0]['id_product']);						
				//executa a acao
				$this->confirmations[] = "<button data-dismiss='alert' class='close' type='button'>x</button>Produto:ID:".$linkstoredata[0]['id_product']."| Dados atualizados com sucesso!";													
			}
			
			
			return true;
		}
		
		public function regenerateThumbnails($type = 'products', $format ='my_format', $deleteOldImages)
		{
			return $this->_regenerateThumbnails($type, $format, $deleteOldImages);
		}
		
		public function _regenerateNewImages($dir, $type, $productsImages = false)
		{
			if (!is_dir($dir)) {
				return false;
			}
			
			$generate_hight_dpi_images = (bool) Configuration::get('PS_HIGHT_DPI');
			
			if (!$productsImages) {
				$formated_medium = ImageType::getFormattedName('medium');
				foreach (scandir($dir, SCANDIR_SORT_NONE) as $image) {
					if (preg_match('/^[0-9]*\.jpg$/', $image)) {
						foreach ($type as $k => $imageType) {
							// Customizable writing dir
							$newDir = $dir;
							if (!file_exists($newDir)) {
								continue;
							}
							
							if (($dir == _PS_CAT_IMG_DIR_) && ($imageType['name'] == $formated_medium) && is_file(_PS_CAT_IMG_DIR_ . str_replace('.', '_thumb.', $image))) {
								$image = str_replace('.', '_thumb.', $image);
							}
							
							if (!file_exists($newDir . substr($image, 0, -4) . '-' . stripslashes($imageType['name']) . '.jpg')) {
								if (!file_exists($dir . $image) || !filesize($dir . $image)) {
									$this->errors[] = $this->trans('Source file does not exist or is empty (%filepath%)', array('%filepath%' => $dir . $image), 'Admin.Design.Notification');
									} elseif (!ImageManager::resize($dir . $image, $newDir . substr(str_replace('_thumb.', '.', $image), 0, -4) . '-' . stripslashes($imageType['name']) . '.jpg', (int) $imageType['width'], (int) $imageType['height'])) {
									$this->errors[] = $this->trans('Failed to resize image file (%filepath%)', array('%filepath%' => $dir . $image), 'Admin.Design.Notification');
								}
								
								if ($generate_hight_dpi_images) {
									if (!ImageManager::resize($dir . $image, $newDir . substr($image, 0, -4) . '-' . stripslashes($imageType['name']) . '2x.jpg', (int) $imageType['width'] * 2, (int) $imageType['height'] * 2)) {
										$this->errors[] = $this->trans('Failed to resize image file to high resolution (%filepath%)', array('%filepath%' => $dir . $image), 'Admin.Design.Notification');
									}
								}
							}
							// stop 4 seconds before the timeout, just enough time to process the end of the page on a slow server
							if (time() - $this->start_time > $this->max_execution_time - 4) {
								return 'timeout';
							}
						}
					}
				}
				} else {
				foreach (Image::getAllImages() as $image) {
					$imageObj = new Image($image['id_image']);
					$existing_img = $dir . $imageObj->getExistingImgPath() . '.jpg';
					if (file_exists($existing_img) && filesize($existing_img)) {
						foreach ($type as $imageType) {
							if (!file_exists($dir . $imageObj->getExistingImgPath() . '-' . stripslashes($imageType['name']) . '.jpg')) {
								if (!ImageManager::resize($existing_img, $dir . $imageObj->getExistingImgPath() . '-' . stripslashes($imageType['name']) . '.jpg', (int) $imageType['width'], (int) $imageType['height'])) {
									$this->errors[] = $this->trans(
                                    'Original image is corrupt (%filename%) for product ID %id% or bad permission on folder.',
                                    array(
									'%filename%' => $existing_img,
									'%id%' => (int) $imageObj->id_product,
                                    ),
                                    'Admin.Design.Notification'
									);
								}
								
								if ($generate_hight_dpi_images) {
									if (!ImageManager::resize($existing_img, $dir . $imageObj->getExistingImgPath() . '-' . stripslashes($imageType['name']) . '2x.jpg', (int) $imageType['width'] * 2, (int) $imageType['height'] * 2)) {
										$this->errors[] = $this->trans(
                                        'Original image is corrupt (%filename%) for product ID %id% or bad permission on folder.',
                                        array(
										'%filename%' => $existing_img,
										'%id%' => (int) $imageObj->id_product,
                                        ),
                                        'Admin.Design.Notification'
										);
									}
								}
							}
						}
						} else {
						$this->errors[] = $this->trans(
                        'Original image is missing or empty (%filename%) for product ID %id%',
                        array(
						'%filename%' => $existing_img,
						'%id%' => (int) $imageObj->id_product,
                        ),
                        'Admin.Design.Notification'
						);
					}
					if (time() - $this->start_time > $this->max_execution_time - 4) { // stop 4 seconds before the tiemout, just enough time to process the end of the page on a slow server
						return 'timeout';
					}
				}
			}
			
			return (bool) count($this->errors);
		}
		
		
		public function _deleteOldImages($dir, $type, $product = false)
		{
			if (!is_dir($dir)) {
				return false;
			}
			$toDel = scandir($dir, SCANDIR_SORT_NONE);
			
			foreach ($toDel as $d) {
				foreach ($type as $imageType) {
					if (preg_match('/^[0-9]+\-' . ($product ? '[0-9]+\-' : '') . $imageType['name'] . '\.jpg$/', $d)
                    || (count($type) > 1 && preg_match('/^[0-9]+\-[_a-zA-Z0-9-]*\.jpg$/', $d))
                    || preg_match('/^([[:lower:]]{2})\-default\-' . $imageType['name'] . '\.jpg$/', $d)) {
						if (file_exists($dir . $d)) {
							unlink($dir . $d);
						}
					}
				}
			}
			
			// delete product images using new filesystem.
			if ($product) {
				$productsImages = Image::getAllImages();
				foreach ($productsImages as $image) {
					$imageObj = new Image($image['id_image']);
					$imageObj->id_product = $image['id_product'];
					if (file_exists($dir . $imageObj->getImgFolder())) {
						$toDel = scandir($dir . $imageObj->getImgFolder(), SCANDIR_SORT_NONE);
						foreach ($toDel as $d) {
							foreach ($type as $imageType) {
								if (preg_match('/^[0-9]+\-' . $imageType['name'] . '\.jpg$/', $d) || (count($type) > 1 && preg_match('/^[0-9]+\-[_a-zA-Z0-9-]*\.jpg$/', $d))) {
									if (file_exists($dir . $imageObj->getImgFolder() . $d)) {
										unlink($dir . $imageObj->getImgFolder() . $d);
									} 
								}
							}
						}
					}
				}
			}
		}
		
		
		public function _regenerateThumbnails($type = 'all', $deleteOldImages = false)
		{
			$this->start_time = time();
			//ini_set('max_execution_time', $this->max_execution_time); // ini_set may be disabled, we need the real value
			@ini_set('max_execution_time', '1200');
			$this->max_execution_time = (int)ini_get('max_execution_time');
			$languages = Language::getLanguages(false);
			
			$process =
			array(
			array('type' => 'products', 'dir' => _PS_PROD_IMG_DIR_),
			);
			
			// Launching generation process
			foreach ($process as $proc)
			{
				if ($type != 'all' && $type != $proc['type'])
				continue;
				
				// Getting format generation
				$formats = ImageType::getImagesTypes($proc['type']);
				if ($type != 'all')
				{
					$format = strval(Tools::getValue('format_'.$type));
					if ($format != 'all')
					foreach ($formats as $k => $form)
					if ($form['id_image_type'] != $format)
					unset($formats[$k]);
				}
				
				if ($deleteOldImages)
				$this->_deleteOldImages($proc['dir'], $formats, ($proc['type'] == 'products' ? true : false));
				if (($return = $this->_regenerateNewImages($proc['dir'], $formats, ($proc['type'] == 'products' ? true : false))) === true)
				{
					if (!count($this->errors))
					$this->errors[] = sprintf(Tools::displayError('Cannot write %s images. Please check the folder\'s writing permissions %s.'), $proc['type'], $proc['dir']);
				}
				elseif ($return == 'timeout')
				$this->errors[] = Tools::displayError('Only part of the images have been regenerated. The server timed out before finishing.');
				else
				{
					if ($proc['type'] == 'products')
					if ($this->_regenerateWatermark($proc['dir']) == 'timeout')
					$this->errors[] = Tools::displayError('Server timed out. The watermark may not have been applied to all images.');
					if (!count($this->errors))
					if ($this->_regenerateNoPictureImages($proc['dir'], $formats, $languages))
					$this->errors[] = sprintf(
					Tools::displayError('Cannot write "No picture" image to (%s) images folder. Please check the folder\'s writing permissions.'),
					$proc['type']
					);
				}
			}
			return (count($this->errors) > 0 ? false : true);
		}
		public function descmercado($id)
		{
			//seleciona os dados do linkstore
			$linkstoredata = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'linkstoreps WHERE IDa = "'. $id .'"');
			//procura produto previamente importado
			$prd = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'product WHERE id_product = "'. $linkstoredata[0]['id_product'] .'"');
			//
			if(!empty($prd[0]['id_product'])){
				//atualiza os dados
				if($linkstoredata){
					if(Configuration::get('IMPORT_PRICE_LINKSTORE'))
					$price =  $this->porcentagem_xn ( Configuration::get('IMPORT_PRICE_LINKSTORE'), $linkstoredata[0]['PRECIOD4'] );
					else 
					$price =  $linkstoredata[0]['PRECIOLISTA'];
					
					Db::getInstance()->update('product',array(
					'reference' => $linkstoredata[0]['CODIGO'],
					'quantity' => $linkstoredata[0]['CANTIDAD'],
					'id_category_default' => $linkstoredata[0]['id_category'],
					'id_tax_rules_group' => Configuration::get('LINKSTORE_TAX_RULE_GROUP'),
					'wholesale_price' => $linkstoredata[0]['PRECIOD4'],
					'price' => $price), '`id_product` = '.$linkstoredata[0]['id_product']);
					Db::getInstance()->update('product_shop',array(	'wholesale_price' => $linkstoredata[0]['PRECIOD4'],'price' => $price), '`id_product` = '.$linkstoredata[0]['id_product']);	
					Db::getInstance()->update('stock_available',array('quantity' => $linkstoredata[0]['CANTIDAD']), '`id_product` = '.$linkstoredata[0]['id_product']);	
					
					//atualiza o codigo do mercadolivre e da descricao
					$position = Db::getInstance()->ExecuteS('SELECT MAX(position) FROM `'._DB_PREFIX_.'category_product` WHERE id_category ='.$linkstoredata[0]['id_category']);
					
					$sqli = array(
								'id_category' => $linkstoredata[0]['id_category'],
								'id_product' => $linkstoredata[0]['id_product'],
								'position' => $position[0] ['MAX(position)'] +1
							);
					Db::getInstance()->Insert('category_product',$sqli);
					//executa a acao
					$this->confirmations[] = "<button data-dismiss='alert' class='close' type='button'>x</button>Produto:ID:".$linkstoredata[0]['id_product']."| Dados atualizados com sucesso!".$linkstoredata[0]['id_category']."-".$prd[0]['id_category_default'];						
					
				} else {
					$this->displayWarning('Falha na atualizacao do produto: ' . $linkstoredata[0]['CODIGO'] .', o item nao foi localizado');
				}	
			} else {
				$this->displayWarning('O produto: ' . $linkstoredata[0]['CODIGO'] .' nao foi importado!');
				
			}			
			return true;
		}
		
		public function limpiar($id)
		{
			//seleciona os dados
			Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'linkstoreps` SET `id_product` = NULL WHERE `IDa` = '.(int)$id);

					//executa a acao
					$this->confirmations[] = "<button data-dismiss='warning' class='close' type='button'>x</button>Produto:ID:".$linkstoredata[0]['IDa']."| Producto desvinculado con sucesso!";
			return true;
		}
		
		public function importimg($imurl,$prodid,$nome,$imgdefault)
		{
			//****imagem do produto
			$mainid_pr = 1;
			/*Imagem principal**/
			$mainimageType = ImageType::getImagesTypes('products');
			$mainimage = new Image();
			$mainimage->id_product = (int)($prodid);
			$mainimagelegend = substr($nome,0,128);
			
			$languages = Language::getLanguages();	
			foreach ($languages as $language) 
			{
				$mainimage->legend[$language['id_lang']] = $mainimagelegend;
			}
			
			$mainimage->position = Image::getHighestPosition($mainid_pr)+1;
			/*verifica imagem padrăo*/
			
			$mainimage->cover = $imgdefault;
			
			if($imurl)
			{
				if (!Db::getInstance()->getValue('SELECT * FROM '._DB_PREFIX_.'image WHERE id_product="'.pSQL($prodid).'" and position ="'.pSQL($mainimage->position).'" and cover ="'.pSQL($mainimage->cover).'"'))
				{
					if ($mainimage->add()) {
                            	    if (empty($shops))
		                  		        $shops = Shop::getContextListShopID();
                                	    $mainimage->associateTo($shops);
                                        if (!$this->copyImg($prodid, $mainimage->id, $imurl,'products'))
                                            Tools::displayError('Error copying image: ');
                                    }
					            
				    //$mainimage->data = file_get_contents ( $imurl );
				    /*$mainimage->add();
				    
				    
				    $path = $mainimage->getPathForCreation();
					$img = $path .'-'.stripslashes('home_default').'.jpg';
					file_put_contents($img, file_get_contents($imurl));
					*/
				}
			}
			
			return $mainimage->id;
			
		}
		
		//****funcao de imagens
		public function copyImg($id_entity, $id_image = null, $url, $entity = 'products', $regenerate = true)
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
			//$url = str_replace(' ', '%20', trim($url));
			
			//**** Evaluate the memory required to resize the image: if it's too much, you can't resize it.
			if (!ImageManager::checkImageMemoryLimit($url))
			return false;
			
			//**** 'file_exists' doesn't work on distant file, and getimagesize make the import slower.
			//**** Just hide the warning, the traitment will be the same.
			
			if (Tools::copy($url, $tmpfile))
			{
				
				ImageManager::resize($tmpfile, $path.'.jpg');
				$images_types = ImageType::getImagesTypes($entity);
				
				if ($regenerate){
					foreach ($images_types as $image_type)
					{
						ImageManager::resize($tmpfile, $path.'-'.stripslashes($image_type['name']).'.jpg', $image_type['width'], $image_type['height']);
						if (in_array($image_type['id_image_type'], $watermark_types))
						Hook::exec('actionWatermark', array('id_image' => $id_image, 'id_product' => $id_entity));
					}
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
	}					