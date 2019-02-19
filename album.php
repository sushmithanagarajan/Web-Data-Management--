<?php
//echo "<title> Photo Album Application </title>";
//echo "<h1><center> Photo - Album Application using DropBox</center> </h1> ";
// display all errors on the browser
error_reporting(E_ALL);
ini_set('display_errors','On');
require_once 'demo-lib.php';
demo_init(); // this just enables nicer output

// if there are many files in your Dropbox it can take some time, so disable the max. execution time
set_time_limit( 0 );

require_once 'DropboxClient.php';

/** you have to create an app at @see https://www.dropbox.com/developers/apps and enter details below: */
/** @noinspection SpellCheckingInspection */
$dropbox = new DropboxClient( array(
	'app_key' => "t7o9yolpg09stt4",      // Put your Dropbox API key here
	'app_secret' => "7ame60z91msjwcj",   // Put your Dropbox API secret here
	'app_full_access' => false,
) );
/**
 * Dropbox will redirect the user here
 * @var string $return_url
 */
$return_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?auth_redirect=1";

// first, try to load existing access token
$bearer_token = demo_token_load( "bearer" );

if ( $bearer_token ) {
	$dropbox->SetBearerToken( $bearer_token );
	//echo "loaded bearer token: " . json_encode( $bearer_token, JSON_PRETTY_PRINT ) . "\n";
} elseif ( ! empty( $_GET['auth_redirect'] ) ) // are we coming from dropbox's auth page?
{
	// get & store bearer token
	$bearer_token = $dropbox->GetBearerToken( null, $return_url );
	demo_store_token( $bearer_token, "bearer" );
} elseif ( ! $dropbox->IsAuthorized() ) {
	// redirect user to Dropbox auth page
	$auth_url = $dropbox->BuildAuthorizeUrl( $return_url );
	die( "Authentication required. <a href='$auth_url'>Continue.</a>" );
}

if(isset($_GET["delete"])){
	$fileName = $_GET["delete"];
	$fileArray = $dropbox->GetFiles("",false);

	foreach($fileArray as $key=>$value){
		if((string)$fileName == (string)$key){
			$dropbox->Delete($value->path); 
			echo "Image Deleted";
		}
 	}
 }

if(isset($_FILES["fileToUpload"])){
	$target_dir = "C:/xampp/htdocs/project6/";
	$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
	if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
		$dropbox->UploadFile($target_file);
		unlink($target_file);
} 
} 

?>
<html>
<head>
<title> Photo Album Application </title>
		<style>
            table{ 
                border-collapse: collapse;
            }  
            .data tr, .data td,.data th{
                border: 1px solid black;
            }
        </style>
</head>
<body>
<h1><center> Photo - Album Application using DropBox</center> </h1>
<div>
<form action="<?= $_SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data">           
<h2>Image Upload<h2><input type="file" name="fileToUpload"/>
<br>
<input type="submit" value="Upload" name="upload"/>
</form>
</div>
<div> 
<h2> Image List: </h2>
<table class="data">
                <thead>
                    <th style="width: 150px;">Name</th>
                    <th  style="width: 150px;">Show</th>
                    <th  style="width: 40px;">Delete</th>
                </thead>
                <tbody> 
<?php
 $fileList = $dropbox->GetFiles("",false);               
?>
<?php 
$file="";	    
$i=0;
foreach($fileList as $key=>$value){
?>
<tr>

<td><?php echo $key;?></td>
<td><a href="album.php?show=<?php echo $key;?>"><?php echo $key;?></a></td>
<td><a href="album.php?delete=<?php echo $key;?>"><button type="button">Delete</button></a>
</td>
</tr>
<?php   
 }   
?>
</tbody>
</table>
</div>
<div id="simg" style=" margin-left: 10px; margin-right: 10px; margin-top: 20px; height: 100px;width:100px">
<?php 
if(isset($_GET['show'])){
echo "<h2>Image Viewing Section</h2>";
echo "<img style=\" display: block; margin: 10px;\" src='".$dropbox->GetLink($_GET['show'],false)."'/></br>"; 
 }
?>
</div>
</body>
</html>