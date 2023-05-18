<?php 


	class AdminLinkstorepsdifferenceController extends ModuleAdminController
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
			
			$this->list_id = 'Linkstore';   // nome do campo da funcao Bulk
			$this->lang = false;
			$this->list_no_link = true;
			$this->table = 'linkstoreps';
			$this->identifier = 'IDa';
			$this->addRowAction('gerarpdf');
			$this->_filter = "AND historic IS NOT NULL"; 
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
			
			parent::__construct();
			
		}
		
		
		public function initContent() {
			if (Tools::isSubmit('gerarpdf2')) {
				    Db::getInstance()->update('configuration',array('value' => Tools::getValue('Cat')), '`id_configuration` = '.Tools::getValue('id_configuration'));
						$this->displayWarning('la categoría ha sido sincronizada para importacion en massa!');
				}
			// Atualizar descricao
			if (Tools::isSubmit('gerarpdf')) {
				if(!empty(Tools::getValue('Category'))){
					    Db::getInstance()->update('Intcomex',array('id_category' => Tools::getValue('Category')), '`ID` = '.Tools::getValue('ID'));
						$this->displayWarning('la categoría ha sido sincronizada!');
				} else {			
					$productml = Tools::getValue('ID');
					$this->descmercado($productml);
				}
			} else {
				// Varios pedidos selecionados
				if ($this->action == 'bulkimportar') {
					foreach (Tools::getValue('IntcomexBox') as $id){
						$status = "importar";
						$this->statusmercado($id);
					}
				}
				//
				if ($this->action == 'bulkatualizar') {
					foreach (Tools::getValue('IntcomexBox') as $id) {
						$status = "atualizar";
						$this->descmercado($id);	
					}
				}
				if ($this->action == 'bulklimpiar') {
					
					foreach (Tools::getValue('IntcomexBox') as $id) 
					{
						
						$status = "limpiar";
						$this->limpiar($id);
					}
				}				
			}
							
			parent::initContent();

		}

		public function initPageHeaderToolbar() {
			
			$adminlinkimport = Context::getContext()->link->getAdminLink('AdminLinkstorepsdifference', true, []);
			
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
                $this->context->controller->addJqueryUI('ui.autocomplete');
				//tratamento ddo historico
				$hist = explode('|',$idd[0]['historic']);
				$oldprice = $hist[0];
				$newprice = $hist[1];
				$percentualprice = $hist[2];
				$this->context->smarty->assign(array(
					    'img' => $img[0],
						'id' => $id,
						'oldprice' => $oldprice,
						'newprice' => $newprice,
						'percentualprice' => $percentualprice,
						'differ' => $idd[0]['historic']
				));
			}
			return $this->context->smarty->fetch(_PS_MODULE_DIR_.'linkstoreps/views/templates/admin/list_action_gerarcategoriad.tpl');
		}
}					
