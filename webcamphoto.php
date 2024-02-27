<?
shell_exec("sudo -u root fswebcam -r 640x480 --scale 640x480 -s Brightness=12 -s Gain=50 -s Contrast=24 --no-banner /tmp/webcam.jpg");
$tmp = file_get_contents("/tmp/webcam.jpg");
shell_exec("rm /tmp/webcam.jpg");
header("Content-Type: image/jpeg");
exit($tmp);
?>