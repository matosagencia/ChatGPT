<?php

include '../../../../config/config.inc.php';

if( $_GET['categoria'] ){
Configuration::updateValue('LINKSTORE_CAT', $_GET['categoria']);

echo 'Categoria salva com sucesso';

?>
<head>
    <meta charset="utf-8">

		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<link rel="icon" type="image/x-icon" href="/store/img/favicon.ico" />
		<link rel="apple-touch-icon" href="/store/img/app_icon.png" />

		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="robots" content="NOFOLLOW, NOINDEX">
		<title>
			store  (PrestaShop&trade;)
		</title>
					<link href="/store/admin123/themes/default/public/theme.css" rel="stylesheet" type="text/css" media="all" />
					<link href="/store/admin123/themes/default/css/overrides.css" rel="stylesheet" type="text/css" media="all" />
							<script type="text/javascript" src="/store/js/jquery/jquery-1.11.0.min.js"></script>
					<script type="text/javascript" src="/store/js/jquery/jquery-migrate-1.2.1.min.js"></script>
					<script type="text/javascript" src="/store/js/jquery/plugins/jquery.validate.js"></script>
					<script type="text/javascript" src="/store/js/vendor/spin.js"></script>
					<script type="text/javascript" src="/store/js/vendor/ladda.js"></script>
				<script type="text/javascript" src="../js/admin/login.js?v=1.7.4.4"></script>
<style>
body {
    background-image: url("../../img/linkstore.jpg");
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
},1000);
</script>
</head>
<?php
} else {
?>
<head>
    <meta charset="utf-8">

		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<link rel="icon" type="image/x-icon" href="/store/img/favicon.ico" />
		<link rel="apple-touch-icon" href="/store/img/app_icon.png" />

		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="robots" content="NOFOLLOW, NOINDEX">
		<title>
			store  (PrestaShop&trade;)
		</title>
					<link href="/store/admin123/themes/default/public/theme.css" rel="stylesheet" type="text/css" media="all" />
					<link href="/store/admin123/themes/default/css/overrides.css" rel="stylesheet" type="text/css" media="all" />
							<script type="text/javascript" src="/store/js/jquery/jquery-1.11.0.min.js"></script>
					<script type="text/javascript" src="/store/js/jquery/jquery-migrate-1.2.1.min.js"></script>
					<script type="text/javascript" src="/store/js/jquery/plugins/jquery.validate.js"></script>
					<script type="text/javascript" src="/store/js/vendor/spin.js"></script>
					<script type="text/javascript" src="/store/js/vendor/ladda.js"></script>
				<script type="text/javascript" src="../js/admin/login.js?v=1.7.4.4"></script>
<style>
body {
    background-image: url("../../img/linkstore.jpg");
    background-repeat: repeat-x;
}
</style>
</head>
<?php
//menu de categorias
$cats = Category::getCategories( (int)Context::getContext()->language->id, true, false);
$categoria =  "<select class='filter fixed-width-sm' name='categoria' disable='disable'><option value='0' selected='selected'>Seleccionar categoría</option>";
		foreach ($cats as $key => $cate)
		{ 
    	$categoria2 .= "<option value='".$cate['id_category']."'>".$cate['id_category']." ->".$cate['name']."</option>";
   		} 
$categoria3 =  "</select> ";	
?>
<form action="#" >
		<fieldset class="btn-group">
<?php    
echo $categoria . $categoria2 . $categoria3;
?>
		<button type="submit">
			Salvar
		</button>		
		</fieldset>
		</form>

<?php
}
?>