<?php
	$code = $_GET['code'];
	if($code){
		$url = "https://foursquare.com/oauth2/access_token?client_id=RPDGSYODY5PV43QBK0VAEIEHDT3J5XQWCTCVUMRBUG3MFPK1&client_secret=OTIARNLSZOFHAXHCTLWG2VGOHXBJNAUJGOWIGS5LTDN5QJW4&grant_type=authorization_code&redirect_uri=http://rak.ulyssespopple.com/auth.php&code=".$code;
		
		$response = get_json_as_array($url);
		$token = $response[access_token];
		$user_req = get_json_as_array("https://api.foursquare.com/v2/users/self?oauth_token=" . $token . "&v=20120929");
		$user = $user_req[response][user];
	}
   
   	function get_json_as_array($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		$result = curl_exec($ch);
		curl_close($ch);
		
		return json_decode($result, true);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<script>
	if("" == "<?php echo $user[id]; ?>"){
		alert("Sign in to Foursquare again");
		window.location.href = "http://rak.ulyssespopple.com/";
	}
	$.ajax( { url: "https://api.mongolab.com/api/1/databases/rak/collections/users?apiKey=50678d1fe4b0c0cda3668e9b",
         data: JSON.stringify( {
			 	"fsid" : "<?php echo $user[id]; ?>",
			 	"first_name" : "<?php echo $user[firstName]; ?>",
			 	"last_name" : "<?php echo $user[lastName]; ?>",
				"access_token" : "<?php echo $token; ?>"
			} ),
         type: "POST",
         contentType: "application/json" } );
		 
	
</script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>R. A. K.</title>
<link href="http://twitter.github.com/bootstrap/assets/css/bootstrap.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>

<div class="container">
	<div class="header_container">
    	<div class="lead"> 
        	Find someone at: <br /> <span id="venueName"></span>.
        </div>
        <div class="stats">
        	<div>
        		<p><span id="venueHereNow"></span></p>
                <p>People</p>
            </div>
            
        	<div class="hideable">
        		<p><span id="venueRAKs"></span></p>
                <p>R.A.Ks</p>
            </div>
            
        	<div id="latestRAK" class="hideable">
            	<p style="font-size: 16px;">Latest R.A.K</p>
        		<p><span id="RAKDate"></span></p>
                <p>"<span id="RAKMessage"></span>"</p>
            </div>
        </div>
        
    </div>
    <div class="form"> 
    	<form id="donate_form" oninput="donation_amount.value=parseFloat(amount_range.value).toFixed(2)">
            <fieldset>
                <textarea id="message" rows="4" cols="50">Message</textarea>
                <label>Slide to give!</label>
                <input type="range" id="amount_range" value="0" min="0" max="5" step="0.05" required="required" />
                
                <p id="dollar_display">$<output id="donation_amount" name="donation_amount">0.00</output></p>
                
            </fieldset>
            <input class="btn btn-primary btn-large" id="RAK_submit" type="submit" value="R.A.K" />
        </form>
    </div>

</div>



<script>
	var venueData, venueId;
	$.getJSON("https://api.foursquare.com/v2/users/self/checkins?sort=newestfirst&limit=1&oauth_token=<?php echo $token; ?>&v=20120929", function(data){
				venueId = data.response.checkins.items[0].venue.id;
				$("#venueName").text(data.response.checkins.items[0].venue.name);
				$.getJSON("https://api.foursquare.com/v2/venues/" + venueId + "/herenow?limit=50&oauth_token=<?php echo $token; ?>&v=20120929", function(data){
					$("#venueHereNow").text(data.response.hereNow.items.length);
					venueData = data;
				});
				
				$.getJSON("https://api.mongolab.com/api/1/databases/rak/collections/kindness?q={'venue_id':'" + venueId + "'}&c=true&apiKey=50678d1fe4b0c0cda3668e9b", function(data){
							$("#venueRAKs").text(data);
				} );
	
				$.getJSON("https://api.mongolab.com/api/1/databases/rak/collections/kindness?s={_id:-1}&q={'venue_id':'" + venueId + "'}&apiKey=50678d1fe4b0c0cda3668e9b", function(data){
						if(data == null){
							$(".hideable").hide();
						} else {
							$("#RAKDate").text(data[0].date);
							$("#RAKMessage").text(data[0].message);
						}
				} );
	} );
	
	


	$("#donate_form").submit(function(e){
		e.preventDefault();
		
		var amount = $("#donation_amount").val();
		var doneeId = -1;
		do{
			var doneeCount = Math.floor(Math.random() * (venueData.response.hereNow.items.length));
			if(venueData.response.hereNow.items.length < 1){
				alert("You need to check in again to see all the people here!");
				return false;
			}
			doneeId = venueData.response.hereNow.items[doneeCount].user.id;
			if(doneeId == <?php echo $user[id]; ?>) continue;
			var venmoId;
			var doneeFirstName = venueData.response.hereNow.items[doneeCount].user.firstName;
			var doneeLastName = venueData.response.hereNow.items[doneeCount].user.lastName;
			
			$.post("venmoreq.php", {fs_id : doneeId}, 
				function(data){
					
					data = $.parseJSON(data);
					
					if(data.data[0] == null){
						venmoId = -1;
						return false;
					}
					venmoId = data.data[0].username;
					
					$.ajax( { url: "https://api.mongolab.com/api/1/databases/rak/collections/kindness?apiKey=50678d1fe4b0c0cda3668e9b",
						 data: JSON.stringify( {
								"donerId" : "<?php echo $user[id]; ?>",
								"first_name" : "<?php echo $user[firstName]; ?>",
								"last_name" : "<?php echo $user[lastName]; ?>",
								"doneeId" : doneeId,
								"doneeFirstName" : doneeFirstName,
								"doneeLastName" : doneeLastName,
								"venmoId" : data.data[0].username,
								"venue_id" : venueId,
								"message" : $("#message").val(),
								"date" : "<?php echo date("d/m/Y"); ?>"
							} ),
						 type: "POST",
						 contentType: "application/json",
						 success:function(){
							
							var url = "https://venmo.com?txn=Pay";
							url += "&recipients=" + data.data[0].username;
							url += "&amount=" + amount;
							url += "&note=" + $("#message").val();
							
							window.location.href=url; 
						 }} );
					
				});
		} while(venmoId == -1);
		
		return false;
	});
</script>
</body>
</html>
