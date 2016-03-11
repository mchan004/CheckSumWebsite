<?php
$DBH = new PDO("sqlite:checksum.db");
$DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if (isset($_POST['Wfile']))
{
	$sql = "DELETE FROM md5_file";
	$STH = $DBH->prepare($sql);
	$STH->execute();
}
function InsertDB($file,$md5) {
	global $DBH;
	$sql = "INSERT INTO md5_file (file,md5) VALUES ('$file','$md5')";
	$STH = $DBH->prepare($sql);
	$STH->execute();
}
function SelectDB($file) {
	global $DBH;
	$sql = "SELECT * FROM md5_file WHERE file = '$file'";
	$STH = $DBH->prepare($sql);
	$STH->execute();
	$row = $STH->fetch();
	return $row;
}

$pathLen = 0;
function prePad($level)
{
	$ss = "";
	for ($ii = 0;  $ii < $level;  $ii++)
	{
		$ss = $ss . "|&nbsp;&nbsp;";
	}
	return $ss;
}

function myScanDir($dir, $level, $rootLen)
{
	global $pathLen;
	global $fp;
	if ($handle = opendir($dir)) {
		$allFiles = array();
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				if (is_dir($dir . "/" . $entry))
				{
					$allFiles[] = "D: " . $dir . "/" . $entry;
				}
				else
				{
					$allFiles[] = "F: " . $dir . "/" . $entry;
				}
			}
		}
		closedir($handle);
		natsort($allFiles);
		foreach($allFiles as $value)
		{
			$displayName = substr($value, $rootLen + 4);
			$fileName    = substr($value, 3);
			$linkName    = str_replace(" ", "%20", substr($value, $pathLen + 3));
			if (is_dir($fileName)) {
				echo prePad($level) . $linkName . "<br>\n";
				myScanDir($fileName, $level + 1, strlen($fileName));
			} else {
				if ($displayName=="checksum.db") continue;
				$md5file = md5_file($fileName);
				echo prePad($level) . "<a href=\"" . $linkName . "\" style=\"text-decoration:none;\">" . $displayName . "</a> Sum: ".$md5file;
				$old_MD5 = SelectDB($fileName)[1];
				
				if (!isset($_POST['Wfile']))
					if ($old_MD5 != $md5file)
						echo " <b><font color='red'>This file has been changed has change: $old_MD5</font></b>";
				
				if (isset($_POST['Wfile']))
					InsertDB($fileName,$md5file);
				echo "<br>";
			}
		}
	}
}

?><!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Site Map</title>
</head>

<body>
	<h1>Site Map</h1>
	<form method="post" action="">
		Save this MD5-file to database.
			<input type="submit" name="Wfile" value="Save">
	</form>
	<p style="font-family:'Courier New', Courier, monospace; font-size:small;">
		<?php
		//Can change to your directory
		$root = getcwd();
		$pathLen = strlen($root);
		myScanDir($root, 0, strlen($root));
		?>
	</p>
</body>
</html>