<?php

require ('../../../config/config.inc.php');
$product = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'product WHERE IDa IS NOT NULL');
$count = 0;
$supplierName = "Linkstore";
	$supplierId = SupplierCore::getIdByName($supplierName);
foreach($product as $prod){
	$prd = new Product($prod['id_product']);

    $count ++;
                $id_product = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'linkstoreps` WHERE `CODIGO` = "'.trim($prd->reference).'"');

                if(!empty($id_product)){
    			    Db::getInstance()->update('linkstoreps',array('id_product' => $prod['id_product'],'id_category' => $prod['id_category_default']), '`IDa` = '.$id_product[0]['IDa']);	
    			    Db::getInstance()->update('product',array('IDa' => $id_product[0]['IDa'], 'id_supplier' => $supplierId), '`id_product` = '.$prod['id_product']);	
  //  			    echo "Produto Sincronizado com sucesso! ". $prod['reference'] ."|>|".$prod['id_product'] ."</br>";
					//atualiza o codigo do linkstoreps e da descricao
					$position = Db::getInstance()->ExecuteS('SELECT MAX(position) FROM `'._DB_PREFIX_.'category_product` WHERE id_category ='.$prd->id_category_default);

					$sqli = array(
								'id_category' => $prd->id_category_default,
								'id_product' => $prd->id,
								'position' => $position[0] ['MAX(position)'] +1
							);
					Db::getInstance()->Insert('category_product',$sqli);

                }
}
$linkstore = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'linkstoreps');
foreach($linkstore as $linkst){
$idproduct = Db::getInstance()->getValue('SELECT id_product FROM `'._DB_PREFIX_.'product` WHERE `id_product` = "'.$linkst['id_product'].'"');
                if(empty($idproduct))
    			    Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'linkstoreps` SET `id_product` = NULL WHERE `IDa` = '.(int)$linkst['IDa']);

}

	header('Location: '.$_GET['adminlink'].'&token='.$_GET['token']);

?>