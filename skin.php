<?php
/*
Title:         Minecraft Avatar
Details:       Based on source by Jamie Bicknell, reworked for Wynncraft by Stefan Cole (Jamdoggy)
Original URL:  http://github.com/jamiebicknell/Minecraft-Avatar
Author:        Jamie Bicknell / Stefan Cole
Twitter:       @jamiebicknell / @Jamdog

Usage:  <img src="skin.php?type=&user=&size=&acc=">
GET Variables (see line 210 for defaults): 
  type - can be body, face or full.  Body = head/shoulders
  user - the IGN username of the player
  size - The size (in pixels) of the final image (length of one side of the square image) - avoid making too big
  acc  - Show accessories layer?  Must be y or n
*/

function get_skin($user = 'char')
{
    $output = @file_get_contents('http://skins.minecraft.net/MinecraftSkins/' . $user . '.png');
    if ($output == '') {
	  // If we can't get the skin from minecraft.net, use the skin server, which may be outdated...
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, 'http://s3.amazonaws.com/MinecraftSkins/' . $user . '.png');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);
      $output = curl_exec($ch);
      $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      if ($status != '200') {
        // Default Skin: http://www.minecraft.net/skin/char.png
        $output = 'iVBORw0KGgoAAAANSUhEUgAAAEAAAAAgCAMAAACVQ462AAAABGdBTUEAALGPC/xhBQAAAwBQTFRFAAAAHxALIxcJJBgIJBgKJhg';
        $output .= 'LJhoKJxsLJhoMKBsKKBsLKBoNKBwLKRwMKh0NKx4NKx4OLR0OLB4OLx8PLB4RLyANLSAQLyIRMiMQMyQRNCUSOigUPyoVKCgoP';
        $output .= 'z8/JiFbMChyAFtbAGBgAGhoAH9/Qh0KQSEMRSIOQioSUigmUTElYkMvbUMqb0UsakAwdUcvdEgvek4za2trOjGJUj2JRjqlVkn';
        $output .= 'MAJmZAJ6eAKioAK+vAMzMikw9gFM0hFIxhlM0gVM5g1U7h1U7h1g6ilk7iFo5j14+kF5Dll9All9BmmNEnGNFnGNGmmRKnGdIn';
        $output .= '2hJnGlMnWpPlm9bnHJcompHrHZaqn1ms3titXtnrYBttIRttolsvohst4Jyu4lyvYtyvY5yvY50xpaA////AAAAAAAAAAAAAAA';
        $output .= 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
        $output .= 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
        $output .= 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
        $output .= 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
        $output .= 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
        $output .= 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
        $output .= 'AAAAAAAAAAAAAAAAAAAAAPSUN6AAAAQB0Uk5T/////////////////////////////////////////////////////////////';
        $output .= '//////////////////////////////////////////////////////////////////////////////////////////////////';
        $output .= '//////////////////////////////////////////////////////////////////////////////////////////////////';
        $output .= '///////////////////////////////////////////////////////////////////////////////////AFP3ByUAAAAYdEV';
        $output .= 'YdFNvZnR3YXJlAFBhaW50Lk5FVCB2My4zNqnn4iUAAAKjSURBVEhLpZSLVtNAEIYLpSlLSUITLCBaGhNBQRM01M2mSCoXNUURI';
        $output .= 'kZFxQvv/wz6724Wij2HCM7J6UyS/b+dmZ208rsww6jiqo4FhannZb5yDqjaNgDVwE/8JAmCMqF6fwGwbU0CKjD/+oAq9jcM27g';
        $output .= 'xAFpNQxU3Bwi9Ajy8fgmGZuvaGAcIuwFA12CGce1jJESr6/Ot1i3Tnq5qptFqzet1jRA1F2XHWQFAs3RzwTTNhQd3rOkFU7c0D';
        $output .= 'ijmohRg1TR9ZmpCN7/8+PX954fb+sTUjK7VLKOYi1IAaTQtUrfm8pP88/vTw8M5q06sZoOouSgHEDI5vrO/eHK28el04yxf3N8';
        $output .= 'ZnyQooZiLfwA0arNb6d6bj998/+vx8710a7bW4E2Uc1EKsEhz7WiQBK9eL29urrzsB8ngaK1JLDUXpYAkGSQH6e7640fL91dWX';
        $output .= 'jxZ33138PZggA+Sz0WQlAL4gmewuzC1uCenqXevMPWc9XrMX/VXh6Hicx4ByHEeAfRg/wtgSMAvz+CKEkYAnc5SpwuD4z70PM+';
        $output .= 'hUf+4348ixF7EGItjxmQcCx/Dzv/SOkuXAF3PdT3GIujjGLELNYwxhF7M4oi//wsgdlYZdMXCmEUUSsSu0OOBACMoBTiu62BdR';
        $output .= 'PEjYxozXFyIpK7IAE0IYa7jOBRqGlOK0BFq3Kdpup3DthFwP9QDlBCGKEECoHEBEDLAXHAQMQnI8jwFYRQw3AMOQAJoOADoAVc';
        $output .= 'DAh0HZAKQZUMZdC43kdeqAPwUBEsC+M4cIEq5KEEBCl90mR8CVR3nxwCdBBS9OAe020UGnXb7KcxzPY9SXoEEIBZtgE7UDgBKy';
        $output .= 'LMhgBS2YdzjMJb4XHRDAPiQhSGjNOxKQIZTgC8BiMECgarxprjjO0OXiV4MAf4A/x0nbcyiS5EAAAAASUVORK5CYII=';
        $output = base64_decode($output);
      }
    }
    return $output;
}
function av_show_body($user='char',$size=80, $inc_acc=TRUE) {
  $skin = get_skin($user);

  $p = ($size / 16);              // Each 'pixel' is magnified to $p pixels across
  $s = floor(($size - $p) / 15);
  $p = floor($size - ($s * 15));
  $h = ($s * 15) + ($p);

  $im = imagecreatefromstring($skin);
  $av = imagecreatetruecolor($size, $h);
  imagesavealpha($av, true);
  imagefill($av, 0, 0, imagecolorallocatealpha($av, 0, 0, 0, 127));

  if (imagesy($im) > 32) {
    // 1.8+ skin
    // Front
    imagecopyresized($av, $im, $s * 4, 0, 8, 8, $s * 8, $s * 8, 8, 8);          // Face
    imagecopyresized($av, $im, $s * 4, $s * 8, 20, 20, $s * 8, $s * 8, 8, 8);   // Torso
    imagecopyresized($av, $im, 0, $s * 8, 44, 20, $s * 4, $s * 8, 4, 8);        // Left Arm
    imagecopyresized($av, $im, $s * 12, $s * 8, 36, 52, $s * 4, $s * 8, 4, 8);  // Right Arm

    // Black Hat Issue
    imagecolortransparent($im, imagecolorat($im, 63, 0));
    if ($inc_acc == TRUE) {
      // Face Accessories
      imagecopyresized($av, $im, $s * 4, 0, 40, 8, $s * 8, $s * 8, 8, 8);

      // Body Accessories
      imagecopyresized($av, $im, $s * 4, $s * 8, 20, 36, $s * 8, $s * 8, 8, 8);

      // Arm Accessores
      imagecopyresized($av, $im, 0, $s * 8, 44, 36, $s * 4, $s * 8, 4, 8);
      imagecopyresized($av, $im, $s * 12, $s * 8, 52, 52, $s * 4, $s * 8, 4, 8);
    }
  } else {
    $mi = imagecreatetruecolor(64, 32);
    imagecopyresampled($mi, $im, 0, 0, 64 - 1, 0, 64, 32, -64, 32);
    imagesavealpha($mi, true);
    imagefill($mi, 0, 0, imagecolorallocatealpha($mi, 0, 0, 0, 127));
    
    // Front
    imagecopyresized($av, $im, $s * 4, 0, 8, 8, $s * 8, $s * 8, 8, 8);
    imagecopyresized($av, $im, $s * 4, $s * 8, 20, 20, $s * 8, $s * 8, 8, 8);
    imagecopyresized($av, $im, 0, $s * 8, 44, 20, $s * 4, $s * 8, 4, 8);
    imagecopyresized($av, $mi, $s * 12, $s * 8, 16, 20, $s * 4, $s * 8, 4, 8);

    // Black Hat Issue
    imagecolortransparent($im, imagecolorat($im, 63, 0));
    if ($inc_acc == TRUE) {
      // Accessories
      imagecopyresized($av, $im, $s * 4, 0, 40, 8, $s * 8, $s * 8, 8, 8);
    }
    imagedestroy($mi);
  }

  header('Content-type: image/png');
  imagepng($av);
  imagedestroy($im);
  imagedestroy($av);
}

function av_show_full($user='char',$size=80,$inc_acc=TRUE) {
  $skin = get_skin($user);

  $p = ($size / 32);
  $s = floor(($size - $p) / 31);
  $p = floor($size - ($s * 31));
  $h = ($s * 31) + $p;
  $x_offset = ($size - (16 * $s)) / 2;  // Centralise
  $y_offset = ($size - (32 * $s)) / 2;  // Centralise

  $im = imagecreatefromstring($skin);
  $av = imagecreatetruecolor($size, $h);
  imagesavealpha($av, true);
  imagefill($av, 0, 0, imagecolorallocatealpha($av, 0, 0, 0, 127));

  if (imagesy($im) > 32) {
    // 1.8+ skin
    // Front
    imagecopyresized($av, $im, $x_offset + $s * 4, $y_offset, 8, 8, $s * 8, $s * 8, 8, 8);                        // Face
    imagecopyresized($av, $im, $x_offset + $s * 4, $y_offset + $s * 8, 20, 20, $s * 8, $s * 12, 8, 12);           // Torso
    imagecopyresized($av, $im, $x_offset, $y_offset + $s * 8, 44, 20, $s * 4, $s * 12, 4, 12);                    // Arm
    imagecopyresized($av, $im, $x_offset + $s * 12, $y_offset + $s * 8, 36, 52, $s * 4, $s * 12, 4, 12);          // Arm
    imagecopyresized($av, $im, $x_offset + $s * 4, $y_offset + $s * 8 + $s * 12, 4, 20, $s * 4, $s * 12, 4, 12);  // Leg
    imagecopyresized($av, $im, $x_offset + $s * 8, $y_offset + $s * 8 + $s * 12, 20, 52, $s * 4, $s * 12, 4, 12); // Leg

    // Black Hat Issue
    imagecolortransparent($im, imagecolorat($im, 63, 0));
    if ($inc_acc == TRUE) {
      // Face Accessories
      imagecopyresized($av, $im, $x_offset + $s * 4, $y_offset, 40, 8, $s * 8, $s * 8, 8, 8);

      // Body Accessories
      imagecopyresized($av, $im, $x_offset + $s * 4, $y_offset + $s * 8, 20, 36, $s * 8, $s * 12, 8, 12);

      // Arm Accessores
      imagecopyresized($av, $im, $x_offset, $y_offset + $s * 8, 44, 36, $s * 4, $s * 12, 4, 12);
      imagecopyresized($av, $im, $x_offset + $s * 12, $y_offset + $s * 8, 52, 52, $s * 4, $s * 12, 4, 12);

      // Leg Accessores
      imagecopyresized($av, $im, $x_offset + $s * 4, $y_offset + $s * 8 + $s * 12, 4, 36, $s * 4, $s * 12, 4, 12);
      imagecopyresized($av, $im, $x_offset + $s * 8, $y_offset + $s * 8 + $s * 12, 4, 52, $s * 4, $s * 12, 4, 12);
    }
  } else {
    $mi = imagecreatetruecolor(64, 32);
    imagecopyresampled($mi, $im, 0, 0, 64 - 1, 0, 64, 32, -64, 32);
    imagesavealpha($mi, true);
    imagefill($mi, 0, 0, imagecolorallocatealpha($mi, 0, 0, 0, 127));
    
    // Front
    imagecopyresized($av, $im, $x_offset + $s * 4, $y_offset, 8, 8, $s * 8, $s * 8, 8, 8);
    imagecopyresized($av, $im, $x_offset + $s * 4, $y_offset + $s * 8, 20, 20, $s * 8, $s * 12, 8, 12);
    imagecopyresized($av, $im, $x_offset, $y_offset + $s * 8, 44, 20, $s * 4, $s * 12, 4, 12);
    imagecopyresized($av, $mi, $x_offset + $s * 12, $y_offset + $s * 8, 16, 20, $s * 4, $s * 12, 4, 12);
    imagecopyresized($av, $im, $x_offset + $s * 4, $y_offset + $s * 8 + $s * 12, 4, 20, $s * 4, $s * 12, 4, 12);
    imagecopyresized($av, $mi, $x_offset + $s * 8, $y_offset + $s * 8 + $s * 12, 56, 20, $s * 4, $s * 12, 4, 12);

    // Black Hat Issue
    imagecolortransparent($im, imagecolorat($im, 63, 0));
    if ($inc_acc == TRUE) {
      // Accessories
      imagecopyresized($av, $im, $x_offset + $s * 4, $y_offset, 40, 8, $s * 8, $s * 8, 8, 8);
    }
    imagedestroy($mi);
  }

  header('Content-type: image/png');
  imagepng($av);
  imagedestroy($im);
  imagedestroy($av);
}

function av_show_face($user='char', $size=80, $inc_acc=TRUE) {
  $skin = get_skin($user);

  $im = imagecreatefromstring($skin);
  $av = imagecreatetruecolor($size, $size);

  imagecopyresized($av, $im, 0, 0, 8, 8, $size, $size, 8, 8);     // Face
  imagecolortransparent($im, imagecolorat($im, 63, 0));           // Black Hat Issue
  if ($inc_acc == TRUE) {
    imagecopyresized($av, $im, 0, 0, 40, 8, $size, $size, 8, 8);    // Accessories
  }
  header('Content-type: image/png');
  imagepng($av);
  imagedestroy($im);
  imagedestroy($av);
}

$t = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'body';                  // Avatar Type       (Default='body')
$u = isset($_REQUEST['user']) ? $_REQUEST['user'] : 'char';                  // User IGN          (Default='char')
$s = isset($_REQUEST['size']) ? max(40, min(2000, $_REQUEST['size'])) : 80;  // Avatar Size       (Range: 40-2000, Default: 80)
$a = isset($_REQUEST['acc']) ? $_REQUEST['acc'] : 'Y';                       // Show Accessories? (Default='Y')

$acc=TRUE;
if (($a=='n') || ($a=='N')) $acc = FALSE;  // Accessories are included unless specifically specified not to

switch ($t) {
  case 'face': av_show_face($u, $s, $acc);
               break;
  case 'body': av_show_body($u, $s, $acc);
               break;
  case 'full': av_show_full($u, $s, $acc);
               break;
  default    : av_show_body($u, $s, $acc);
               break;
}
