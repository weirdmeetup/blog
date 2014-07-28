<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-EN">
	<head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Facebook Page Publish - Fault Diagnosis</title>
        <style type="text/css">
			body {
			font-family: "Lucida Grande", Verdana, Arial, "Bitstream Vera Sans", sans-serif;
			font-size: 14px;
			background-color: #FFF;
			padding: 2em;
			}
			
			img {
			border: 1em #EDEFF4 solid;
			margin: 1em 0em;
			}
			
			h1 {
			background-color: #3B5998;
			color: #FFF;
			font-size: 24px;
			padding: 0.3em;
			}
			
			h2 {
			background-color: #DEDEDE;
			padding: 0.3em;
			font-size: 16px;
			}
			
			a {
			color: red;
			}
			
			.keyword {
			color: gray;
			font-weight: bold;
			}
			
			a:target {
			background-color: yellow;
			}
		</style>
	</head>
	<body>
        <h1>Facebook Page Publish - Fault Diagnosis</h1>
        <p>In order for the plugin to work, every test should return a positive response. Errors are mostly due to server limitations or misconfiguration. If you observe an error, try the compatibility options on the plugin settings page and/or post your test results in the <a href="http://wordpress.org/tags/facebook-page-publish">forum</a>.</p>
        <h2>Check if your server can connect to Facebook</h2>
        <p>Sends a https request to Facebook to detect possible connection errors. Expects a code 400 / Bad request response.</p>
        <?php
			
			$api_url = 'https://graph.facebook.com/oauth/access_token?client_id=fake-id&client_secret=fake-id&redirect_uri=http://fake-uri.com';
			
			$response = file_get_contents($api_url);
			$pos = strpos($response, 'error');
			
			if ($pos !== false) {
				echo '<h3 style="color:red">There seems to be a problem</h3>';
				echo '<p>Try enabling the compatibility options of the plugin</p>';
				} else {
				echo '<h3 style="color:green">Everything looks fine</h3>';
			}
			
			echo '<pre style="font-size:8pt">';
			print_r($response);
			echo '</pre>';
		?>
        
        <h2>Check if the SSL module is loaded</h2>
        <p>Facebook requires secure https transmissions. Therefore your webserver has to support SSL. Not all hoster, especially freehoster, offer this service.</p>
        <?php
			function check_https1() {
                ob_start();
                phpinfo(INFO_GENERAL);
                $phpinfo = ob_get_contents();
                ob_end_clean();
                $s = strpos($phpinfo,'Registered PHP Streams');
                $e = strpos($phpinfo, "\n", $s);
                return strstr(substr($phpinfo, $s, $e - $s), 'https');
			}
			
			function check_https2() {
                ob_start();
                phpinfo(INFO_GENERAL);
                $test = strstr(ob_get_contents(), 'https');
                ob_end_clean();
                return ($test == true);
			}
			
			if (!check_https1() and !check_https2()) {
                echo '<h3 style="color:red">SSL module not detected.</h3>';
                echo '<p><em>Note: This test does not detect all possible SSL modules and configurations</em></p>';
				} else {
                echo '<h3 style="color:green">Everything looks fine</h3>';
			}
		?>
	</body>
</html>