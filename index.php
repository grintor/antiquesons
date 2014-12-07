<?php
	$bcGUID   = "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx";
	$bcPWD    = "xxxxxxxxxxx";
	$BTCprice = json_decode(file_get_contents("https://blockchain.info/ticker"), true);
?>

<html>
	<body>
		<?php
			if (!$_GET) {
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
			if ($_GET["choice"] == "1") {
				$USDprice = 0.15;
				$BTCprice = round($USDprice / $BTCprice["USD"]["last"] * 1000000);
				print("Miley Cyrus - Wrecking Ball costs $$USDprice ($BTCprice bits) <br />");
			}
			if ($_GET["choice"] == "2") {
				$USDprice = 0.25;
				$BTCprice = round($USDprice / $BTCprice["USD"]["last"] * 1000000);
				print("PSY - GANGNAM STYLE costs $$USDprice ($BTCprice bits) <br />");
			}
			if ($_GET["choice"] == "3") {
				$USDprice = 0.35;
				$BTCprice = round($USDprice / $BTCprice["USD"]["last"] * 1000000);
				print("Katy Perry - Roar costs $$USDprice ($BTCprice bits) <br />");
			}
			
			if ($_GET["choice"]) {
				$satoshi = $BTCprice * 100;
				$expires = time() + 3600;
				$product = $_GET["choice"];
				$BTCaddress = json_decode(file_get_contents("https://blockchain.info/merchant/$bcGUID/new_address?password=$bcPWD"), true);
				$BTCaddress = $BTCaddress[address];
				$hamc = hash_hmac('sha256', "$expires-$product-$BTCaddress-$satoshi" , $bcPWD);
				$url = "?paid=true&expires=$expires&BTCaddress=$BTCaddress&satoshi=$satoshi&product=$product&hmac=$hamc";
				print("Send this ammount to $BTCaddress <br />");
				print("Do not refresh or leave this page! <br />");
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
			if ($_GET["addressbalance"]){
				print(file_get_contents("https://blockchain.info/q/addressbalance/".$_GET["addressbalance"]."?confirmations=0"));
			}
			if ($_GET["paid"]){
				$expires = $_GET["expires"];
				$BTCaddress = $_GET["BTCaddress"];
				$satoshi = $_GET["satoshi"];
				$product = $_GET['product'];
				$hamc = $_GET["hmac"];
				if (time() > $expires) {
					print("This URL has expired!");
				} else {
					if($hamc != hash_hmac('sha256', "$expires-$product-$BTCaddress-$satoshi" , $bcPWD)) {
						print("Invalid URL!");
					} else {
						if(intval(file_get_contents("https://blockchain.info/q/addressbalance/$BTCaddress?confirmations=0")) >= $satoshi) {
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
							print("Stop that!");
						}
					}
				}
			}
		?>
	</body>
</html>