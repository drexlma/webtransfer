<?php
ob_start();
require '../main.php';
cleanFiles();

if(isset($_GET['id'],$_GET['ac'],$_GET['uid'],$_GET['uc']) && isset($_POST['btn_downoad'])){

	$file = getFile($_GET['id'],$_GET['ac']);
	if($file !== false){
		if(md5($file['file_id'].'/'.$_GET['uid']) != $_GET['uc']){
			// echo md5($file['file_id'].'/'.$_GET['uid']) ." != ".$_GET['uc'].'<br>';
			die('UID Error');
		}
		$absender = getAbsender($file['file_id']);
		$empfanger = getEmpfanger($file['file_id'],$_GET['uid']);
		

		if(file_exists($file['path'])){
			header("Cache-Control: ");
			header("Pragma: ");
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.basename($file['name']).'"');
			header('Content-Length: ' . filesize($file['path']));
			ob_end_clean();
			ob_end_flush() ;
			$handle = fopen($file['path'], "rb");
			$contents = '';
			while (is_resource($handle) && $handle && !feof($handle)) {
				echo fread($handle, 8192);
			}
			fclose($handle);
		
			setFileDownloadComplete($file,$empfanger,$absender);
			if($file['haltbarkeit'] == '0'){
				deleteDBFile($file['file_id']);
			}
		} else {
			echo 'fehler!';
		}
		
	}


	exit;
} 

?>
<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="SPURENELEMENTE GmbH">

    <title>WebTransfer</title>
    <link href="/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="/bootstrap/css/se.css" rel="stylesheet">
	<meta name="robots" content="noindex, NOFOLLOW">
    <!--[if lt IE 9]>
      <script src="/bootstrap/js/html5shiv.js"></script>
    <![endif]-->
    
      <script src="/bootstrap/js/se.js"></script>
    <style>
		<?php if(!empty($css)){ echo $css ; } ?>
		@media screen and (min-width: 1024px) {
			body{
				margin-bottom:50px;
				<?php 
				
				if(empty($background_img)){
					$background_img = '/img/bg.jpg';
				}  
				
				if(empty($background_color)){
					$background_img = '#FFFFFF';
				}  
				?>
				background: <?php echo $background_color;?> url('<?php echo $background_img?>') no-repeat center center fixed;

 				webkit-background-size: cover;
				-moz-background-size: cover;
				-o-background-size: cover;
				background-size: cover;
			}
		}
		
	</style>
  </head>
  <body>
    <div id="wrap">
      <div class="container">
      	<h1>WebTransfer (bis <?php echo getMaxUploadSize(); ?>)</h1>
        
<?php 
if(isset($_GET['id'],$_GET['ac'])){
	$file = getFile($_GET['id'],$_GET['ac']);
	if($file !== false){?>

		?>
		<form  role="form"  enctype="multipart/form-data" method="POST">
		<div class="form-group">
			<label for="file">Datei:</label>
			<?php echo $file['name']?>
		  </div>
			<button type="submit" name="btn_downoad" class="btn btn-default">Datei runterladen</button>

		</form>
	<?php 
	} else { 
		// header 404
		echo 'Datei wurde nicht gefunden';
	} 

} elseif(isset($_FILES['frm_userfile'],$_POST['frm_ownMail'])){

	$uploaddir = '../files/';
	$uploadfile = $uploaddir .date('Y.m.d.H.i.s').'-'. basename($_FILES['frm_userfile']['name']);
	$_POST['frm_extMail'];
	$_POST['frm_ownMail'];
	// $_POST['frm_pwd'];
	$_POST['frm_message'];
	if($_POST['frm_haltbarkeit'] > 70 && $_POST['frm_haltbarkeit'] <0){
		$_POST['frm_haltbarkeit'] = 31;
	} else {
		$_POST['frm_haltbarkeit'] = (int) $_POST['frm_haltbarkeit'];
	}
	if($mail_allow_owner && count($mail_allow_owner) > 0){
		$allow_found = false;
		foreach($mail_allow_owner as $k => $v){
			if(strpos($_POST['frm_ownMail'],$v) === false){
			
			} else {
				$allow_found = true;
			}
			
			if(strpos($_POST['frm_extMail'],$v) === false){
			
			} else {
				$allow_found = true;
			}
			
		}
	} else {
		$allow_found = true;
	}
	
	//var_dump($allow_found);
	if(!$allow_found){
		echo '<div class="alert alert-danger">Die E-Mail Adresse ist nicht erlaubt. Bitte Systemadministrator kontaktieren!</div>'."\n";
	
	} else if(filter_var($_POST['frm_extMail'], FILTER_VALIDATE_EMAIL) === false){
		echo '<div class="alert alert-danger">Die E-Mail Adresse ist falsch!</div>'."\n";

	}elseif(filter_var($_POST['frm_ownMail'], FILTER_VALIDATE_EMAIL) === false){ 
		echo '<div class="alert alert-danger">Die E-Mail Adresse ist falsch!</div>'."\n";

	}elseif (move_uploaded_file($_FILES['frm_userfile']['tmp_name'], $uploadfile)) {
		$accesscode = md5(time().$_FILES['frm_userfile']['tmp_name']. $_SERVER['REMOTE_ADDR']).md5(time().$uploadfile);
		$data = array(
			'path' => $uploadfile,
			'name' => basename($_FILES['frm_userfile']['name']),
			'adddate' => time(),
			'ip' => $_SERVER['REMOTE_ADDR'],
			'accesscode' => $accesscode,
			'haltbarkeit' => $_POST['frm_haltbarkeit'],
			'password' => '' // $_POST['frm_pwd']
				
		);
		$GLOBALS['oop_db']->insert('file',$data);
		$fileid = $GLOBALS['oop_db']->get_last_insert_id();

		$data = array(
				'file_id' => $fileid,
				'adddate' => time(),
				'remote_addr' => $_SERVER['REMOTE_ADDR'],
				'mail' => $_POST['frm_ownMail']
		);
		$GLOBALS['oop_db']->insert('absender',$data);
		

		
			$data = array(
					'file_id' => $fileid,
					'adddate' => time(),
					'mail' => $_POST['frm_extMail']
			);
			$GLOBALS['oop_db']->insert('empfanger',$data);
			$empfanger_id = $GLOBALS['oop_db']->get_last_insert_id();
			

			$link = 'http://'.$_SERVER['HTTP_HOST'].'/file/'.$fileid.'/'.$accesscode.'/'.$empfanger_id.'/'.md5($fileid.'/'.$empfanger_id).'/';
			
			$vars =  array(	
						'size' => humanSize(filesize($uploadfile)), 
						'datei' => basename($_FILES['frm_userfile']['name']), 
						'link' => $link,
						'haltbarkeit' => $_POST['frm_haltbarkeit'] , 
						'nachricht' => $_POST['frm_message'] ,
						'absender' => $_POST['frm_ownMail'],
						'empfanger' => $_POST['frm_extMail']
					);

			sendMail(
				$_POST['frm_extMail'],
				'FileUploadextMail',
				$vars
			);
			sendMail(
				$_POST['frm_ownMail'],
				'FileUploadIntMail',
				$vars
			);
			if($mail_admin_uploadinfo){
				sendMail(
					$mail_admin_uploadinfo,
					'FileUploadIntMail',
					$vars
				);
			}
		
		
		
		echo '<div class="alert alert-success">';
		echo 'Die Datei "'.basename($_FILES['frm_userfile']['name']).'" wurde erfolgreich hochgeladen und an den Empfänger gesendet.<br>';
		if($_POST['frm_haltbarkeit'] > 0){
			echo 'Haltbarkeit: '.$_POST['frm_haltbarkeit'].' Tage ';
		} else {
			// echo 'Haltbarkeit: Datei wird nach dem runterladen direkt gelöscht ';
			
		}
		echo '<br></div>'."\n";
	} else {
		echo '<div class="alert alert-danger">Dateiupload ist fehlgeschlagen</div>'."\n";
	}

} else {?>    

<script type="text/javascript">
 
 
var client = null;

function uploadFile()
{
	try{
		//Wieder unser File Objekt
		var file = document.getElementById("file").files[0];
		//FormData Objekt erzeugen
		var formData = new FormData();
		//XMLHttpRequest Objekt erzeugen
		   client = new XMLHttpRequest();
 
		if(!file)
			return false;
 

 
		//Fügt dem formData Objekt unser File Objekt hinzu
		formData.append("frm_userfile", file);
		formData.append("frm_ownMail", document.getElementById("ownMail").value);
		formData.append("frm_extMail", document.getElementById("extMail").value);
		formData.append("frm_haltbarkeit", document.getElementById("haltbarkeit").value);
		formData.append("frm_message", document.getElementById("message").value);
		formData.append("frm_xhr", 1);
	
 
		client.onerror = function(e) {
			alert("onError");
		};
 
		client.onload = function(e) {
			document.body.innerHTML = this.responseText;
		};
 
		client.upload.onprogress = function(e) {
			var progress = Math.round(100 / e.total * e.loaded);
		
			document.getElementById('progress-wrap').style.display = 'block';
			document.getElementById('progressbar').style.width = progress +'%';         
			document.getElementById("prozsatz").innerHTML = progress + "%";
		};
 
		client.onabort = function(e) {
			alert("Upload abgebrochen");
		};
 
		client.open("POST", "index.php");
		client.send(formData);
		return true;
    } catch (e) {
    	return false;
    }
}
 
function uploadAbort() {
    if(client instanceof XMLHttpRequest)
        client.abort();
}
</script>
 

	<!-- FILE UPLUAD PROGRESS -/- START -->
	<div id="progress-wrap" style="display:none">
		Datei <span id="filename"></span> wird hochgeladen.		<div class="progress">
		  <div class="progress-bar" id="progressbar" role="progressbar" style="width: 0%;">
		    <span class="sr-only" id="prozsatz">0% complete</span>
		  </div>
		</div>
	</div>
	<!-- FILE UPLUAD PROGRESS -/- END -->
	    
<form action="" role="form" enctype="multipart/form-data" method="POST"">
        <p>
        	Sie können hier sehr große Dateien an Ihren Kunden schicken. 
        	Dazu wird hier die Datei hochgeladen und ein Download-Link an die E-Mail Adresse versendet. 
        	Dieser kann die Datei anschließend runterladen. Sie werden per E-Mail darüber benachrichtigt. 
        </p>

  <div class="form-group">
    <label for="file">Datei *</label>
    <input type="file"  name="frm_userfile" class="form-control" id="file" required >
  </div>
        
  <div class="form-group">
    <label for="ownMail">Eigene E-Mail* </label>
    <input type="email" name="frm_ownMail" class="form-control" id="ownMail" placeholder="Eigene E-Mail*" required>
  </div>    
            
  <div class="form-group">
    <label for="extMail">E-Mail des Geschäftspartners* </label>
    <input type="email"  name="frm_extMail" class="form-control" id="extMail" placeholder="E-Mail des Geschäftspartners*" required>
  </div>
            
  <div class="form-group">
    <label for="haltbarkeit">Haltbarkeit</label>
    <select  name="frm_haltbarkeit" class="form-control" id="haltbarkeit" >
    	<option value="0"> Datei nach dem Download  löschen</option>
    	<option value="1"> 1 Tag haltbar</option>
    	<option value="3"> 3 Tag haltbar</option>
    	<option value="5" selected> 5 Tag haltbar</option>
    	<option value="7"> 7 Tag haltbar</option>
    	<option value="14"> 14 Tag haltbar</option>
    	<option value="31"> 31 Tag haltbar</option>
    	<option value="62"> 2 Monate haltbar</option>
    </select>
  </div>
  
 <!-- 
  <div class="form-group">
    <label for="pwd">Passwort</label>
    <input type="password" name="frm_pwd" class="form-control" id="pwd">
  </div>
 -->   
  <div class="form-group">
    <label for="message">Nachricht</label>
   <textarea rows="8" cols="8" name="frm_message" class="form-control" id="message" placeholder="Ihre Nachricht"></textarea> 
  </div>

    <button type="submit" class="btn btn-success" onclick="return !uploadFile();">hochladen & per E-Mail verschicken</button>
    
  
 </form>    
 
 <?php } ?>   

	<div id="logo"></div>
</div>
<div id="footer">
<a href="http://www.se-medien.de" target="_blank">v 3.0</a> &middot;
<a target="_blank" href="<?php echo $url_impressum;?>">Impressum</a>
</div>
</body>
</html>