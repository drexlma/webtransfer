<?php 
require('../main.php');
?>
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="<?php echo ini_get("session.upload_progress.name"); ?>" value="123" />

<input type="submit" />
</form>
<?php 
print_r($_SESSION);
$_SESSION['time'] = time();;
?>