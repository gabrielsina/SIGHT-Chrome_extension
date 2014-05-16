//SIGHT System - Summary Generation
//Chrome Extension v1.0
//By Gabriel Sina - Last update May 16, 2014
/////////////////////////////////////////////

//We don't need to import jquery here, in chrome extensions, because we set it up on the manifest file
//jQuery = loadLibraries("jquery-1.4.2.min.js");

//create a new WebSocket object.
var wsUri = "ws://localhost:8888/new_socket/new_server.php";
websocket = new WebSocket(wsUri);

//#### Message received from server
websocket.onmessage = function(ev) {
	var msg = JSON.parse(ev.data); //PHP sends Json data
	var type = msg.type; //message type
	var output = msg.output; //message text
	var action = msg.action; //action {connected or disconnected}
	var mrp = msg.mrp; //most relevant paragraph number

	//in case the message is a positive connection
	if (type=='system' && action =='connected'){
		alert("System message: "+ action);
		send_data(string_image, string_content);
	}
	//in case the message is an output of the VEM or SIGHT systems.
	if (type=='output'){
		//creates a new window and writes the output on it
		var popup = window.open("","SIGHT System - Summary", "width=400, height=400, left=200, top=200");
		popup.document.write("<br>"+output+" MRP: "+mrp+"| Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.");
	}
	//in case the client is disconnected from the socket
	if (type=='system' && action =='disconnected'){
		alert("System message: "+ action);
	}

};

//default listeners to trigger functions in case of errors or closing websocket
websocket.onerror	= function(ev){alert("Error Occurred - "+ev.data);};
websocket.onclose 	= function(ev){alert("Connection Closed");connected=false;};

//before the window is actually closed, we close the client socket
window.onbeforeunload = function() {
    websocket.onclose = function () {}; // disable onclose handler first
    websocket.close();
};


//found_images variable stores the url of every image that fit into the constraints
var found_images = [];
//runs over images DOM element and decides whether the image is relevant to the article
//for(var i = 0; i < document.images.length; i++)
for(var i = 0; i < 1; i++)
{
	if(document.images[i].width>='70')
	{
		//appends the image into the found_images list
		found_images.push(document.images[i].src);
	}
}

//page_content variable stores the text content found in HTML tags h1, h2, and p.
var page_content = [];

$("h1").each(function(){
	var text = $(this).text();
	if(text!=""){ page_content.push(text); }
});

$("h2").each(function(){
	var text = $(this).text();
	if(text!=""){ page_content.push(text); }
});

$("p").each(function(){
	var text = $(this).text();
	if(text!=""){ page_content.push(text); }
});

//Brute forcing both arrays to be one single string
//because images should be only one in current cases
//TODO: Adapt when application goes to open web, so it will require the PERL script and the PHP server-socket to handle arrays!
var string_image = found_images.join("");
var string_content = page_content.join("\n");

//sends data to server socket
function send_data(found_images, page_content){
	var myfound_images = found_images; //get found images
	var mypage_content = page_content.replace(/'/, ""); //get page content
	//gets file name+extension, in order to have graph ID available
	//Graph ID should not be needed in future versions, for the open web
	var page_name = window.location.pathname.substr(window.location.pathname.lastIndexOf("/") + 1);
	var myid = page_name.replace(/.html|.htm|.php|.asp/, ""); //removes extension
		
	//prepare json data
	var msg = {
		text: mypage_content,
		image: myfound_images,
		graphid: myid
	};
	//convert and send data to server
	var json = JSON.stringify(msg);
	websocket.send(json);
}


