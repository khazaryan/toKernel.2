<?php 
/*
 * toKernel - Universal PHP Framework.
 * Error 404 template file.
 * 
 * Note: All variables in this file defined in application error_404() method.
 * file - /tokernel.framework/kernel/e.http.class.php
 *
 * @category   templates
 * @package    application
 *
 * Restrict direct access to this file
 */
defined('TK_EXEC') or die('Restricted area.');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{var.title}</title>
<style>
body {
	background-color:#F5F5F5;
	padding:0px;
}
.main_div {
	margin-top:20px;
	border:#D4D4D4 solid 1px; 
	background-color:#FFFFFF;
	padding:20px;
}
.error_div {
	border-top:2px solid  #CCCCCC;
	border-bottom:2px solid #CCCCCC;
	background-color:#F4F4F4; 
	font-weight:bold;
	padding-left:10px;
	padding-right:10px;
	font-family:Arial, Helvetica, sans-serif;
}
.signature {
	font-family: Arial, Helvetica, sans-serif;
	font-weight: bold;
	font-size:14px;
}
.signature:link {
	font-family: Arial, Helvetica, sans-serif;
	font-weight: bold;
	font-size:14px;
	text-decoration:none;
}
.signature:active {
	font-family: Arial, Helvetica, sans-serif;
	font-weight: bold;
	font-size:14px;
	text-decoration:none;
	color:#000000;
}
.signature:visited {
	font-family: Arial, Helvetica, sans-serif;
	font-weight: bold;
	font-size:14px;
	text-decoration:none;
	color:#000000;
}
.signature:hover {
	font-family: Arial, Helvetica, sans-serif;
	font-weight: bold;
	font-size:14px;
	text-decoration:underline;
	color:#000000;
}
</style>
</head>
<body>
<!-- widget addon="usage" module="module2" action="test_top_menu" -->
<div align="center">
	<div align="left" class="main_div">
		<h1>{var.title}</h1>
		<div class="error_div">
			<p>{var.message}</p>
		</div>
		<p>&nbsp;</p>
	</div>
	<p class="signature"><a href="http://www.tokernel.com" class="signature">toKernel - Universal PHP Framework. v<?php echo TK_VERSION; ?></a></p>
</div>
</body>
</html>