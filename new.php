<?php

include('vendor/autoload.php');
include('lib/printInfo.php');
set_time_limit(0);

include('lib/db.php');
$db = db();

if (isset($_REQUEST['checkin'])){

	$cn = $_REQUEST['cn'];
	$pinfo = array();
	$pinfo['meals'] = array();
	$pinfo['card_no'] = $cn;
	$pinfo['amt'] = $_REQUEST['ttldue'];

	$q = sprintf("INSERT INTO registrations VALUES (now(),%d,'%s',
		'','',0,0,0,0)",$cn,$db->escape($_REQUEST['name']));
	$r = $db->query($q);
	for($i=0;$i<count($_REQUEST['am']);$i++){
		$q = sprintf("INSERT INTO regMeals VALUES (%d,'%s',%d)",
			$cn,($i==0?'OWNER':'GUEST'),$_REQUEST['am'][$i]);
		$r = $db->query($q);
		if ($_REQUEST['am'][$i] == 1)
			$pinfo['meals'][] = 'meat';
		elseif($_REQUEST['am'][$i] == 2)
			$pinfo['meals'][] = 'veg';
        elseif ($_REQUEST['am'][$i] == 3)
            $pinfo['meals'][] = 'nmeat';
        else
            $pinfo['meals'][] = 'wveg';
	}

	for($i=0;$i<$_REQUEST['chicken'];$i++){
		$q = "INSERT INTO regMeals VALUES ($cn,'GUEST',1)";
		$r = $db->query($q);
		$pinfo['meals'][] = 'meat';
	}
	for($i=0;$i<$_REQUEST['veg'];$i++){
		$q = "INSERT INTO regMeals VALUES ($cn,'GUEST',2)";
		$r = $db->query($q);
		$pinfo['meals'][] = 'veg';
	}
    for($i=0;$i<$_REQUEST['mgf'];$i++){
        $q = "INSERT INTO regmeals VALUES ($cn,'GUEST',3)";
        $r = $db->query($q);
        $pinfo['meals'][] = 'nmeat';
    }
    for($i=0;$i<$_REQUEST['vgf'];$i++){
        $q = "INSERT INTO regmeals VALUES ($cn,'GUEST',3)";
        $r = $db->query($q);
        $pinfo['meals'][] = 'wveg';
    }
	for($i=0;$i<$_REQUEST['kids'];$i++){
		$q = "INSERT INtO regMeals VALUES ($cn,'CHILD',0)";
		$r = $db->query($q);
		$pinfo['meals'][] = 'kid';
	}
	$q = "UPDATE registrations SET checked_in=1 WHERE card_no=".$cn;
	$r = $db->query($q);
	print_info($pinfo);
	header("Location: index.php");
	exit;
}
else if (isset($_REQUEST['back'])){
	header("Location: index.php");
	exit;
}

$cn = (int)$_REQUEST['cn'];

$q = "SELECT FirstName,LastName FROM custdata
	WHERE personNum=1 AND CardNo=".$cn;
$r = $db->query($q);
$regW = $db->fetch_row($r);
$regW['name'] = $regW['FirstName'].' '.$regW['LastName'];

?>
<script type="text/javascript">
function reCalc(){
	var c = document.getElementById('chicken').value;
	var v = document.getElementById('veg').value;
	var g = document.getElementById('mgf').value;
	var w = document.getElementById('vgf').value;
	var k = document.getElementById('kis').value;
	var b = document.getElementById('basedue').value;

	var due = (c*20) + (v*20) + (g*20) + (w*20) + (k*5) + (1*b);
	document.getElementById('amtdue').innerHTML='$'+due;
	document.getElementById('ttldue').value= due;
}
</script>
<body style="background-color: red;" onload="document.getElementById('chicken').focus();">
<div style="color:white;">DID NOT RSVP</div>
<div>
	<div style="float:left;width:60%;">
	<form method="post" action="new.php">
	<table>
	<tr><th>Name</th><td><input type="text" name="name" value="<?php echo $regW['name']; ?>" /></td></tr>
	<tr><th>Owner Meal</th><td><select name="am[]">
	<option value="0"></option>
	<option value="1" selected>Steak</option><option value="2">Risotto</option><option value="3">Squash</option>
	</select></td></tr>
	<tr><td colspan="2" align="center">Additional Meals</td></tr>
	<tr><th>Pork</th><td><input type="text" name="chicken" id="chicken" value="0" onchange="reCalc(); "/></td></tr>
	<tr><th>Ratatouille</th><td><input type="text" name="veg" id="veg" onchange="reCalc();" value="0" /></td></tr>
	<tr><th>Pork (G/F)</th><td><input type="text" name="mgf" id="mgf" onchange="reCalc();" value="0" /></td></tr>
	<tr><th>Ratatouille (G/F)</th><td><input type="text" name="vgf" id="vgf" onchange="reCalc();" value="0" /></td></tr>
	<tr><th>Spaghetti</th><td><input type="text" name="kids" id="kids" onchange="reCalc();" value="0" /></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><th>Amount Due</th><td id="amtdue">$0</td></tr>
	<input type="hidden" id="basedue" name="basedue" value="0" />
	<input type="hidden" id="ttldue" name="ttldue" value="0" />
	<input type="hidden" name="cn" value="<?php echo $cn; ?>" />
	<tr><td><input type="submit" name="checkin" value="Check In" /></td>
	<td><input type="submit" name="back" value="Go Back" /></td></tr>
	</table>
	</form>
	</div>

	<div style="float:left;width:35%;">
	<table cellspacing="0" cellpadding="4" border="1">
    <tr><th>Meal</th><th>Pending</th><th>Checked-in</th></tr>
    <?php foreach (getArrivedStatus($db) as $row) { ?>
        <tr>
            <td><?php echo $row['typeDesc']; ?></td>
            <td><?php echo $row['pending']; ?></td>
            <td><?php echo $row['arrived']; ?></td>
        </tr>
    <?php } ?>
	</table>
	</div>

	<div style="clear:left;"></div>
</div>
</body>
