<?php
/**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Linkstoreps extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'linkstoreps';
        $this->tab = 'administration';
        $this->version = '1.0.1';
        $this->author = 'bruno de matos';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('link store for prestashop');
        $this->description = $this->l('import catalog from link store to your prestashop');
		$this->_tabClassName[] = array('className' => 'AdminLinkstoreps', 'name' => 'Linkstoreps');
		$this->_tabClassName[] = array('className' => 'AdminLinkstorepsimporter', 'name' => $this->l('Importaciones de productos'));
		$this->_tabClassName[] = array('className' => 'AdminLinkstorepsupdate', 'name' => $this->l('actualización de precio y stock'));

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     *
    public function install()
    {
        Configuration::updateValue('LINKSTOREPS_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionAttributeGroupDelete');
    }

    public function uninstall()
    {
        Configuration::deleteByName('LINKSTOREPS_LIVE_MODE');

        return parent::uninstall();
    }*/

 public function install()
    {

		return parent::install() &&
			$this->criaMenus() &&
			$this->installDb() &&
			$this->alteraTabela() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionAttributeGroupDelete');


    }

    public function uninstall()
    {

		return parent::uninstall() && $this->excluiMenus() && $this->uninstallDb();

    }
    


	public function installDb()
	{
		$return = true;

		$sql = array();													

		$sql[]="CREATE TABLE IF NOT EXISTS "._DB_PREFIX_."linkstoreps (
				`IDa` int(20) UNSIGNED NOT NULL,
				`id_category` varchar(10) DEFAULT NULL,
				`CODIGO` varchar(100) DEFAULT NULL,
				`DESCRIPCION` varchar(600) NOT NULL,
				`CANTIDAD` int(20) DEFAULT NULL,
				`TRANSITO` int(20) DEFAULT NULL,
				`FECHALLEGADA` datetime DEFAULT NULL,  
				`PRECIOLISTA` decimal(20,6) DEFAULT NULL,
				`PRECIOD4` decimal(20,6) DEFAULT NULL,
				`CAT` int(20) DEFAULT NULL,
				`SUBCAT` int(20) DEFAULT NULL,
				`PESO` decimal(20,6) DEFAULT NULL,
				`IMAGEN` text DEFAULT NULL,
				`DESCRIPCION2` text DEFAULT NULL,
				`URLFAB` varchar(600) DEFAULT NULL,
				`FTECNICA` varchar(600) DEFAULT NULL,
				`PREQUERIDO` text DEFAULT NULL,
				`PSUGERIDO` text DEFAULT NULL,
				`IDMANUFACTURE` int(10) DEFAULT NULL,
				`id_product` int(10) DEFAULT NULL,
				`update` datetime DEFAULT NULL,  
				PRIMARY KEY  (`IDa`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

		
		foreach ($sql as $s)
			$return &= Db::getInstance()->execute($s);
		
		return $return;
	}
	
	    public function alteraTabela() {
			$db = Db::getInstance();
			// altera tabela de produtos
			$sqlc = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '"._DB_PREFIX_."product' AND column_name = 'IDa' AND table_schema = '"._DB_NAME_."'";
			$dadosc = $db->getRow($sqlc);			
			
			if (!$dadosc) {
				$sqlsc =   "ALTER TABLE `"._DB_PREFIX_."product` ADD `IDa` varchar(20) DEFAULT 0;";
				$db->Execute($sqlsc);
			}		
			
			// altera tabela de produtos
			$sqlcx = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '"._DB_PREFIX_."manufacturer' AND column_name = 'IDa' AND table_schema = '"._DB_NAME_."'";
			$dadoscx = $db->getRow($sqlcx);			
			
			if (!$dadoscx) {
				$sqlscx =   "ALTER TABLE `"._DB_PREFIX_."manufacturer` ADD `IDa` varchar(20) DEFAULT 0;";
				$db->Execute($sqlscx);
			}
			return true;
	}	
	public function uninstallDb()
	{
		$sql = array();

		$sql[]="DROP TABLE "._DB_PREFIX_."linkstoreps";	
		//$sql[] = "ALTER TABLE "._DB_PREFIX_."product DROP COLUMN IDa;";

		foreach($sql as $sq){
			Db::getInstance()->Execute($sq);
		}	
		return true;
	}
	
	function criaMenus() {
        
        // Cria tab principal
        $main_tab = new Tab();
        $main_tab->class_name = $this->_tabClassName[0]['className'];

        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            $main_tab->name[$language['id_lang']] = $this->_tabClassName[0]['name'];
        }

        $main_tab->id_parent = 0;
        $main_tab->module = $this->name;
        $main_tab->add();

        // Cria sub tabs do menu
        for ($i = 1; $i < count($this->_tabClassName); $i++) {

            $tab = new Tab();
            $tab->class_name = $this->_tabClassName[$i]['className'];

            $languages = Language::getLanguages();
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = $this->_tabClassName[$i]['name'];
            }

            $tab->id_parent = $main_tab->id;
            $tab->module = $this->name;
            $tab->add();
        }

        return true;
    }

    function excluiMenus() {
        
        for ($i = 0; $i < count($this->_tabClassName); $i++) {

            $id_tab = Tab::getIdFromClassName($this->_tabClassName[$i]['className']);
            if ($id_tab) {
                $tab = new Tab($id_tab);
                $tab->delete();
            }
        }

        return true;
    }	

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitLinkstorepsModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitLinkstorepsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        
        	
			
            $options2 = array();
			
		foreach (TaxRulesGroup::getTaxRulesGroupsForOptions() as $taxrule)
		{
			$options2[] = array(
								"id_option" => (int)$taxrule[id_tax_rules_group],
								"name" => $taxrule[name]
							);
		}                      
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid username'),
                        'name' => 'LINKSTOREPS_ACCOUNT_EMAIL',
                        'label' => $this->l('Usuario'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'LINKSTOREPS_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'IMPORT_PRICE_LINKSTORE',
                        'label' => $this->l('Commission'),
                        'suffix' => $this->l('%'),
                        'desc' => $this->l('Enter an percentual over price'),
                    ),  
					array(
						'type' => 'select',
						'label' => $this->l('Tax Group Rule:'),
						'name' => 'LINKSTORE_TAX_RULE_GROUP',
						'required' => false,
						'options' => array(
											'query' => $options2,                           
											'id' => 'id_option',                           
											'name' => 'name'  
											
										)
						),
                   
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'LINKSTOREPS_ACCOUNT_EMAIL' => Configuration::get('LINKSTOREPS_ACCOUNT_EMAIL', ''),
            'LINKSTOREPS_ACCOUNT_PASSWORD' => Configuration::get('LINKSTOREPS_ACCOUNT_PASSWORD', ''),
            'IMPORT_PRICE_LINKSTORE' => Configuration::get('IMPORT_PRICE_LINKSTORE',''),

			'LINKSTORE_TAX_RULE_GROUP' => Configuration::get('LINKSTORE_TAX_RULE_GROUP','')
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookActionAttributeGroupDelete()
    {
        /* Place your code here. */
    }
} 
