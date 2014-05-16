//READ-ME
//SIGHT System - Summary Generation
//Chrome Extension v1.0
//By Gabriel Sina - Last update May 16, 2014
/////////////////////////////////////////////
Setting up client and server sides:

#Client side:
SIGHT - Summary Generation, Chrome Extension is the whole "Sight_app folder"
1st: Open Google Chrome Browser.
2nd: In the browser menu, click in "Window", then click "Extensions".
3rd: Close to the top right-hand corner, check the "Developer mode" checkbox.
4th: Click on the "Load unpacked extension..." button.
5th: Choose the path to the "Sight_app" folder.
		The extension should now be listed in your extensions list, make sure the "Enable" checkbox is checked.
		After any changes in any the "Sight_app" folder, refresh this extansions page.

#Server side:
The server side of this application is the folder "new_socket"
1st: Upload the whole folder "new_socket" to the "www" or "htdocs" directory in your web server.
	!!!Make sure your PHP server has "php_socket" extension installed!!!
2nd: from the server terminal, go to the "www/new_socket/" folder;
	type: php -q new_server.php
	the server socket should start running
	if it says "Impossible to bind socket in port XXXX, it's already in use", you will need to change the port in the client application file and in the php server socket script. (Follow steps on #Changing ports)

	P.S.: If php is not a local variable in your system, you will need to go to the php directory and run it from there, giving as argument the new_server.php file with its whole path. [Which will probably be something like "/WAMP/bin/php/php5.5.3/bin/php"]

#Testing:
Once you have both steps above set up properly, you should be able to open "http://localhost/DemoLineGraph/L212.html" (or other articles) and the application will run.
	Remember that the chrome extension will only trigger when you open a page that is inside a web server, so make sure that your graphs htmls are inside the apache/other webserver.

#Changing Ports:
1st: Go to your www or htdocs folder in your webserver, open the folder "new_socket", and then:
	Open the "new_server.php" script and edit the $host variable and/or $port variable.
2nd: Go to the folder you have your Chrome extension linked to the browser, open the file "contentscript2.js".
	the var wsUri should have the same host and port addresses than your server socket php file.
	Also, it should have the same path to the "new_server.php" file as well.
	Example: var wsUri = "ws://localhost:8888/new_socket/new_server.php";