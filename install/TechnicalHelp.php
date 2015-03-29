<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">

<link rel="shortcut icon" type="image/x-icon" href="../images/favicon.ico">
<link rel="stylesheet" type="text/css" media="all" href="../core/css/temsbase.css">
<link rel="stylesheet" type="text/css" media="all" href="../core/css/temsinstall.css">

<title>Time and Expense Management System</title>

</head>

<body>

<div class="base-layer">

	<div id="Page-Header">
		<img class="logo" src='../images/logo.gif' alt="TEMS Logo">
		<span class="app-name">Time and Expense Management System</span>

		<div class="section-heading-layer">Install Database: Technical Help</div>
	
		<div class='end-of-form-notes'>
			<h3><a name="directory-permission">Directory permissions required to install TEMS and operate it:</a></h3>
			<p>Give write permission to <b>core/config</b> folder (chmod core/config 777)</p>
			<p>Give write permission to <b>log</b> folder (chmod log 777)</p>
			<p>After installation is complete, change permission of <b>core/config</b>
				folder back to original (in most cases it is, chmod core/config 755)</p>

			<h3><a name="hosted-server-installation">Installation on a Hosted Server:</a></h3>
			<p>NOTE: This description is for a typical hosting server situation and stated in a generic way. Your situation may be different. <a href="#contact-us">Contact us to discuss your installation issue.</a></p>
			<p>Make sure that you have given proper permission to <b>core/config</b> and <b>log</b> directories. See <a href="#directory-permission">directory permissions section</a> for details.</p>
			<p>Create the database from cpanel and add two users to it, one having dba authority and other read/write permissions only.</p>
			<p>Provide the database name and these two users ids and passwords in the installation process.</p>
			<p>Second user id is recommended, but not mandatory.</p>

			<h3><a name="localhost-installation">Installation on a localhost:</a></h3>
			<p>NOTE: This description is for a typical localhost installation process and stated in a generic way. Your situation may be different. <a href="#contact-us">Contact us to discuss your installation issue.</a></p>
			<p>Make sure that you have given proper permission to <b>core/config</b> and <b>log</b> directories. See <a href="#directory-permission">directory permissions section</a> for details.</p>
			<p>Generally you do not have to create the the database in advance. If you use database admin user id and password that you set during MySQL installation, TEMS will do everything for you.<p>

			<h3><a name="use-existing-database">To use an existing database:</a></h3>
			<p>** CAUTION **: Be extreme careful, before you make any changes. You may corrput the application permanently beyond repair.</p>
			<p>This situation generally occurs when you want to switch between different databases. This is a very unusual situation for non-developer users.</p>
			<p>Follow the direction as described at <a href='http://temsonline.com/content/how-can-i-configure-databaseini-file-manually'>How to re-configure Database.ini manually.</a></p>

			<h3>To upgrade:</h3>
			<p>Contact technical support.</p>

			<h3><a name="contact-us">For installation, configuration and technical supports and traning:</a></h3>
			<p>For support, create a ticket at <a href="http://sourceforge.net/tracker/?group_id=343652&amp;atid=1438060" target="_blank">TEMS Support</a></p>
			<p>If you prefer to contact us by email, our email address is <a href="mailto:contact@temsonlne.com">contact@temsonline.com</a></p>

		</div>
		
		<div class='button'>
			<input type='button' value='Go Back' onclick='window.history.go(-1)'>
		</div>
		
		<div class='Page-Footer'>Copyright 2009-2013 Initechs, LLC.</div>
	</div>
</div>

</body>

</html>