<?php 


	class AdminLinkstorepscommisionController extends ModuleAdminController
	{
		
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
		
		protected $position_identifier = 'ID';
		
		protected $submitted_tabs;
		
		public function __construct()
		{
			$this->bootstrap = true;
			$this->context = Context::getContext();
			$this->className = 'Product';			
			$this->list_id = 'linkstoreps';   // nome do campo da funcao Bulk
			$this->lang = false;
			$this->list_no_link = true;
			$this->table = 'linkstoreps';
			$this->identifier = 'IDa';
			$this->addRowAction('gerarpdf');
			$this->_filter = "AND commision IS NOT NULL"; 
			//$this->_defaultOrderBy = 'Imagenes';
			//$this->_defaultOrderWay = 'ASC';

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
			/* 'PRECIOLISTA' => array(
			'title' => 'Precio Lista',
			'align' => 'left'
            ),	 */		
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
			'commision' => array(
			'title' => '% profit',
			'type'  => 'text',
			'align' => 'left',
			'hint' => 'Tax of profit over price, if undefine will be used the default'
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
			'text' => 'Actualizar Comisión',
			'icon' => 'icon-file-o',
            ),				
			);
			
			parent::__construct();
			
		}
		
		
		public function initContent() {
			
			// Atualizar descricao
			if(Tools::isSubmit('gerarpdf5')){
				$pcoint = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'linkstoreps` WHERE IDa = '.Tools::getValue('IDa').'');
				//conversion dolar echo 
				$pcointc = $pcoint[0]['PRECIOD4'];
				//calc commision
				$pcointcd =  $this->porcentagem_xn ( Tools::getValue('commision'), $pcointc );
				Db::getInstance()->update('linkstoreps',array('commision' => Tools::getValue('commision')), '`IDa` = '.Tools::getValue('IDa'));
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'linkstoreps` SET commision = NULL WHERE commision = ""');
				if(!empty($pcoint[0]['id_product'])){
					Db::getInstance()->update('product',array('price' => $pcointcd), '`id_product` = '.$pcoint[0]['id_product']);
					Db::getInstance()->update('product_shop',array('price' => $pcointcd), '`id_product` = '.$pcoint[0]['id_product']);
				}
				$this->displayWarning('la commision ha sido sincronizada!');
			}
			
			if(Tools::isSubmit('gerarpdf6')){
				Db::getInstance()->update('linkstoreps',array('overstock' => Tools::getValue('overstock')), '`IDa` = '.Tools::getValue('IDa'));
				if(Tools::getValue('overstock') == 1)
					$this->displayWarning('Stock prestashop definido!');
				else
					$this->displayWarning('Stock Linkstore definido!');
			}
			
			if(Tools::isSubmit('gerarpdf7')){
				Configuration::updateValue('COMMISIONMASS', Tools::getValue('commision2'));
			}
			
			if ($this->action == 'bulkimportar'){
				
				foreach (Tools::getValue('linkstorepsBox') as $id){
					$pcoint = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'linkstoreps` WHERE IDa = '.$id.'');
					//conversion dolar echo 
					$pcointc = $pcoint[0]['PRECIOD4'];
					//calc commision
					$pcointcd =  $this->porcentagem_xn ( Configuration::get('COMMISIONMASS'), $pcointc );
					Db::getInstance()->update('linkstoreps',array('commision' => Configuration::get('COMMISIONMASS')), '`IDa` = '.$id);
					Db::getInstance()->update('product',array('price' => $pcointcd), '`id_product` = '.$pcoint[0]['id_product']);
					Db::getInstance()->update('product_shop',array('price' => $pcointcd), '`id_product` = '.$pcoint[0]['id_product']);
					$this->displayWarning('la commision ha sido sincronizada!');
				}
			}
							
			parent::initContent();

		}
		
		// Função de porcentagem: Quanto é X% de N?
		public function porcentagem_xn ( $porcentagem, $total ) 
		{
			$montante = ( $porcentagem / 100 ) * $total;
			return $montante + $total;
		}

		public function renderList()
		{
			$id_configuration = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'configuration` WHERE `name` LIKE "INTCOMEX_MASTER"');
			$catnme = Db::getInstance()->getValue('SELECT `name` FROM `'._DB_PREFIX_.'category_lang` WHERE `id_category` = '.Configuration::get('INTCOMEX_MASTER').' AND `id_lang` = '.(int)($this->context->language->id).'');
			// Here we retrieve the list (without doing any strange thing)
			$list = parent::renderList();
			$categ = Category::getCategories( (int)($this->context->language->id), true, false);
			$idmle = array();
			foreach($categ as $idcate){
			//verifica o parent
				if($idcate['id_category'] > 2){
				    $idmle[] = $idcate;
				}
			}
			
            $this->context->controller->addJqueryUI('ui.autocomplete');

			// Assign some vars to pass to our custom tpl
			$this->context->smarty->assign(
				array( 
					'catnme' => $catnme,
					'valueid' => $id_configuration[0]['value'],
					'idml' => $idmle,
					'urlenvio2' => self::$currentIndex.'&gerarpdf2&token='.($_GET['token'] != null ? $_GET['token'] : $this->token).'&id_configuration='.$id_configuration[0]['id_configuration'].'&Cat',

				)
			);

			// Assign some vars to pass to our custom tpl
			$this->context->smarty->assign(
				array( 
					'commisionmod' => Configuration::get('COMMISIONMASS'),
					'urlenvio4' => self::$currentIndex.'&'.$this->identifier.'='.$id.'&gerarpdf7&token='.($token != null ? $token : $this->token).'&commision2=',					
				)
			);

			// Get the custom tpl rendered
			$content = $this->context->smarty->fetch(_PS_MODULE_DIR_.'linkstoreps/views/templates/admin/links.tpl');

			// return the list plus your content
			return $content . $content2 . $list;
		}
		
		public function initPageHeaderToolbar() {
			
			$adminlinkimport = Context::getContext()->link->getAdminLink('AdminLinkstorepscommision', true, []);
			
			$this->page_header_toolbar_btn['SolicEtiq2'] = array(
            'href' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/linkstoreps/extractor/import.php?adminlink='.$adminlinkimport,
            'desc' => 'Import',
            'icon' => 'process-icon-update'
			);
			
			$this->page_header_toolbar_btn['SolicEtiq3'] = array(
            'href' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/linkstoreps/controllers/admin/updatepriceqty.php?adminlink='.$adminlinkimport,
            'desc' => 'Syn Qty/Precio',
            'icon' => 'process-icon-update'
			);	
			
			$this->page_header_toolbar_btn['SolicEtiq'] = array(
            'href' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/linkstoreps/sync.php?adminlink='.$adminlinkimport,
            'desc' => 'Match con productos existentes',
			
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
				

		// cria gerarpdf action list
		public function displayGerarPDFLink($token = null, $id) { 
			$cat = Category::getCategories( (int)($this->context->language->id), true, false);
			if (!array_key_exists('gerarpdf', self::$cache_lang)) { 
				$idd = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'linkstoreps WHERE IDa = '.(int)$id);
				if(!empty($idd)){
					$catname = Db::getInstance()->getValue('SELECT name FROM '._DB_PREFIX_.'category_lang WHERE id_category = '.(int)$idd[0]['id_category'].' and id_lang = '. $this->context->language->id);
					$img = explode(';',$idd[0]['Imagenes']);
					$this->context->smarty->assign(array('catname' => $catname));
				}
				$idml = array();
				
				foreach($cat as $idcat){
					//verifica o parent
					$parent = Db::getInstance()->getValue('SELECT name FROM '._DB_PREFIX_.'category_lang WHERE id_category = '.(int)$idcat['id_parent'].' and id_lang = '. $this->context->language->id);
					if($idcat['id_category'] > 2){
					    $idml[] = $idcat;
					}
				}
				
				$adminlinkim = Context::getContext()->link->getAdminLink('AdminLinkstorepscommision', true, []);

                $this->context->controller->addJqueryUI('ui.autocomplete');
				$this->context->smarty->assign(array(
						'urlenvio3' => self::$currentIndex.'&'.$this->identifier.'='.$id.'&gerarpdf5&token='.($token != null ? $token : $this->token).'&commision=',
						'commision' => $idd[0]['commision'],
						'idd' => $idd,
						'urlenvio2' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/linkstoreps/controllers/admin/updatepriceqty.php?adminlink='.$adminlinkim.'&'.$this->identifier.'='.$id.'&update=stock&token='.($token != null ? $token : $this->token),
						'overstock' => $idd[0]['overstock'],
						'urlenvio3' => self::$currentIndex.'&'.$this->identifier.'='.$id.'&gerarpdf5&token='.($token != null ? $token : $this->token).'&commision=',
						'urlenvio4' => self::$currentIndex.'&'.$this->identifier.'='.$id.'&gerarpdf6&token='.($token != null ? $token : $this->token).'&overstock=',
						'idml' => $idml,
						'imp' => 0						   	
				));
			}
			return $this->context->smarty->fetch(_PS_MODULE_DIR_.'linkstoreps/views/templates/admin/list_action_commision.tpl');
		}
}					
