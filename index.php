<?php
	$bcGUID   = "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"; //blockchain.info wallet identifier
	$bcPWD    = "xxxxxxxxxxx";                          //blockchain.info password
	
	//curl with output JSON decoded
	function curlJSON($elink){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);  //verify blockchain.info SSL
	curl_setopt($ch, CURLOPT_USERAGENT, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
	curl_setopt($ch, CURLOPT_URL, $elink);
	$ccc = curl_exec($ch);
	$json = json_decode($ccc, true);
	return $json;
	}
	
	//curl with string output
	function curlFGC($elink){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);  //verify blockchain.info SSL
	curl_setopt($ch, CURLOPT_USERAGENT, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
	curl_setopt($ch, CURLOPT_URL, $elink);
	$ccc = curl_exec($ch);
	return $ccc;
	}
	
	
?>
<html>
	<body>
		<?php
			if (!$_GET) {	// if there are no GET variables, they are landing here directly so show them the homepage
				print('
					Choose which to buy:
					<br />
					<a href="?choice=1">Miley Cyrus - Wrecking Ball</a>
					<br />
					<a href="?choice=2">PSY - GANGNAM STYLE</a>
					<br />
					<a href="?choice=3">Katy Perry - Roar</a>
				');
			}
			if ($_GET["choice"]) {																	// If they made any selection, 
				$BTCprice = curlJSON("https://blockchain.info/ticker");	// you need to know the btc price.
			}
			if ($_GET["choice"] == "1") {	// user selection 1
				$USDprice = 0.15;
				$BTCprice = round($USDprice / $BTCprice["USD"]["last"] * 1000000);
				print("Miley Cyrus - Wrecking Ball costs $$USDprice ($BTCprice bits) <br />");
			}
			if ($_GET["choice"] == "2") {	// user selection 2
				$USDprice = 0.25;
				$BTCprice = round($USDprice / $BTCprice["USD"]["last"] * 1000000);
				print("PSY - GANGNAM STYLE costs $$USDprice ($BTCprice bits) <br />");
			}
			if ($_GET["choice"] == "3") {	// user selection 3
				$USDprice = 0.35;
				$BTCprice = round($USDprice / $BTCprice["USD"]["last"] * 1000000);
				print("Katy Perry - Roar costs $$USDprice ($BTCprice bits) <br />");
			}
			if ($_GET["choice"]) {
				$satoshi = $BTCprice * 100;
				$expires = time() + 3600;	// make the generated url only valid for an hour
				$product = $_GET["choice"];
				$BTCaddress = curlJSON("https://blockchain.info/merchant/$bcGUID/new_address?password=$bcPWD");		// we request a new bitcoin address for our wallet
				$BTCaddress = $BTCaddress[address];
				$hamc = hash_hmac('sha256', "$expires-$product-$BTCaddress-$satoshi" , $bcPWD);	// to make sure the URL is not manipulated
				$url = "?paid=true&expires=$expires&BTCaddress=$BTCaddress&satoshi=$satoshi&product=$product&hmac=$hamc";	// this URL has all the info we want to verify in it and is secured by HMAC
				print("Send this ammount to $BTCaddress <br />");
				print("Do not refresh or leave this page! <br />");
				// below we make an iframe where the current balance of the address we generated above is displayed (but the iframe is hidden)
				// a javascript reads the contents of the iframe and when the contents show that the balance is >= the item price, it redirects the page to the product page.
				print('
					<iframe width="0" height="0" style="visibility:hidden;display:none" id="iframe0" src="?addressbalance='.$BTCaddress.'"></iframe>
					<script>
						setInterval(function(){
							if (parseInt(document.getElementById("iframe0").contentWindow.document.body.innerHTML) >= '.$satoshi.') {
								window.location.href = "'.$url.'";
							} else {
								document.getElementById("iframe0").contentWindow.location.reload();
							}
						}, 3000);
					</script>
				');
			}
			if ($_GET["addressbalance"]){	// this is a workaround for the fact that you can't read the contents of an iframe from an external domain using javascript
				print(curlFGC("https://blockchain.info/q/addressbalance/".$_GET["addressbalance"]."?confirmations=0"));
			}
			if ($_GET["paid"]){
				$expires = $_GET["expires"];
				$BTCaddress = $_GET["BTCaddress"];
				$satoshi = $_GET["satoshi"];
				$product = $_GET['product'];
				$hamc = $_GET["hmac"];
				if (time() > $expires) {	// check that the URL is not expired. Remember, they can't manupulate the expire time in the URL because of the HMAC
					print("This URL has expired!");
				} else {
					if($hamc != hash_hmac('sha256', "$expires-$product-$BTCaddress-$satoshi" , $bcPWD)) {	// make sure the URL is not manipulated
						print("Invalid URL!");
					} else {
						if(intval(curlFGC("https://blockchain.info/q/addressbalance/$BTCaddress?confirmations=0")) >= $satoshi) {	// make sure they actually paid
							if ($product == "1") {
								print ('<iframe width="560" height="315" src="//www.youtube.com/embed/My2FRPA3Gf8?list=PLirAqAtl_h2r5g8xGajEwdXd3x1sZh8hC" frameborder="0" allowfullscreen></iframe>');
							}
							if ($product == "2") {
								print ('<iframe width="560" height="315" src="//www.youtube.com/embed/9bZkp7q19f0?list=PLirAqAtl_h2r5g8xGajEwdXd3x1sZh8hC" frameborder="0" allowfullscreen></iframe>');
							}
							if ($product == "3") {
								print ('<iframe width="560" height="315" src="//www.youtube.com/embed/CevxZvSJLk8?list=PLirAqAtl_h2r5g8xGajEwdXd3x1sZh8hC" frameborder="0" allowfullscreen></iframe>');
							}
						} else {
							print("Stop that!");	// they copied and pasted the URL from the page source and tried to navigate to it without paying
						}
					}
				}
			}
		?>
	</body>
</html>