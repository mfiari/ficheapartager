<?php

	DEFINE ("ENVIRONNEMENT", "DEV");

	/* path config */
	DEFINE ("ROOT_PATH", "D:/wamp/www/projets/fiche_partage/website/");
	DEFINE ("MODEL_PATH", ROOT_PATH."models/");
	DEFINE ("WEBSITE_PATH", ROOT_PATH."web/");
	DEFINE ("WEBSERVICES_PATH", ROOT_PATH."webservices/");
	
	/* WEB rootes */
	DEFINE ("WEBSITE_URL", "http://localhost/projets/fiche_partage/website/web/");

	/* Webservices config */
	DEFINE ("WS_URL", "http://localhost/projets/fiche_partage/website/webservice/");
	DEFINE ("WS_AUTH_LOGIN", "");
	DEFINE ("WS_AUTH_PASSWORD", "");
	
	/* MySQL config */
	DEFINE ("MYSQL_HOST", "localhost");
	DEFINE ("MYSQL_DBNAME", "fiche_partage");
	DEFINE ("MYSQL_LOGIN", "root");
	DEFINE ("MYSQL_PASSWORD", "");
	
	/* GCM config */
	DEFINE ("GOOGLE_API_KEY", "AIzaSyA2OFGXi3t5i1_mVyyHBw8OBp20ZsY9Lh0");
	
	/* recaptcha */
	DEFINE ("RECAPTCHA_SITE_KEY", "6LfchhsTAAAAADMbJz4UTn1TvRhwUSLq9tfA4zLt");
	DEFINE ("RECAPTCHA_SECRET_KEY", "6LfchhsTAAAAACbjpNAmPNm0j_1yuyhhY3HpL-T-");
	
	/* LOG config */
	DEFINE ("SQL_LOG", "SQL");
	DEFINE ("VIEW_LOG", "VIEW");
	DEFINE ("SERVER_LOG", "SERVER");
	DEFINE ("WS_LOG", "WS");
	DEFINE ("CRON_LOG", "CRON");
	DEFINE ("MAIL_LOG", "MAIL");
	
	DEFINE ("LOG_LEVEL_INFO", "INFO");
	DEFINE ("LOG_LEVEL_WARNING", "WARNING");
	DEFINE ("LOG_LEVEL_ERROR", "ERROR");
	
	/* Constante */
	DEFINE ("USER_CLIENT", "USER");
	DEFINE ("USER_LIVREUR", "LIVREUR");
	DEFINE ("USER_RESTAURANT", "RESTAURANT");
	DEFINE ("USER_ADMIN", "ADMIN");
	DEFINE ("USER_ADMIN_RESTAURANT", "ADMIN_RESTAURANT");

?>