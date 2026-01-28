<?php
  # Tocheck if exec php function is enable, uncomment following lin and search for function listed in disable_functions directive
  #phpinfo();

  $constant='constant';
  require_once('engine.inc.php');

  #####################
  #      ACTIONS      #
  #####################
  if(isset($_POST['submit_reload'])) {
    unset($_POST);
    clearstatcache();
    sleep(1);
    header("Location: ".$_SERVER['REQUEST_URI']);
  }

  if(isset($_POST['submit_settings'])) {
    header("Location: ".$_SERVER['PHP_SELF']."?usedns=".$_POST['usedns']."&jailnoempty=".$_POST['jailnoempty']."&jailinfo=".$_POST['jailinfo']."&showignored=".$_POST['showignored']);
    unset($_POST);
    clearstatcache();
    sleep(1);
  }

  if(isset($_POST['submit_add'])) {
    $error_ban=ban_unban_ip("banip",$_POST['ban_jail'],$_POST['ban_ip']);
    if($error_ban!='OK') {
      if($error_ban=='nojailselected') {
        $error_ban='<p class="msg_er">'.$nojailselected.'</p>';
      }
      elseif($error_ban=='ipnotvalid') {
        $error_ban='<p class="msg_er">'.$ipnotvalid.'</p>';
      }
      elseif($error_ban=='couldnot') {
        $error_ban='<p class="msg_er">'.$couldnot.'</p>';
      }
    } else {
      $error_ban='<p class="msg_ok">'.$ipsuccessfullybanned.'</p>';
      unset($_POST);
      clearstatcache();
      sleep(1);
    }
  }

  if(isset($_POST['submit_del'])) {
    $error_unban=ban_unban_ip("unbanip",$_POST['unban_jail'],$_POST['unban_ip']);
    if($error_unban!='OK') {
      $error_unban='<p class="msg_er">'.$couldnot.'</p>';
    } else {
      $error_unban='<p class="msg_ok">'.$ipsuccessfullyunbanned.'</p>';
      unset($_POST);
      clearstatcache();
      sleep(1);
    }
  }

  if(isset($_POST['submit_addignore'])) {
    $error_ignore=add_remove_ignoreip("addignoreip",$_POST['ignore_jail'],$_POST['ignore_ip']);
    if($error_ignore!='OK') {
      if($error_ignore=='nojailselected') {
        $error_ignore='<p class="msg_er">'.$nojailselected.'</p>';
      }
      elseif($error_ignore=='ipnotvalid') {
        $error_ignore='<p class="msg_er">'.$ipnotvalid.'</p>';
      }
      elseif($error_ignore=='couldnot') {
        $error_ignore='<p class="msg_er">'.$couldnotignore.'</p>';
      }
    } else {
      $error_ignore='<p class="msg_ok">'.$ipsuccessfullyignored.'</p>';
      unset($_POST);
      clearstatcache();
      sleep(1);
    }
  }

  if(isset($_POST['submit_delignore'])) {
    $error_unignore=add_remove_ignoreip("delignoreip",$_POST['unignore_jail'],$_POST['unignore_ip']);
    if($error_unignore!='OK') {
      $error_unignore='<p class="msg_er">'.$couldnotunignore.'</p>';
    } else {
      $error_unignore='<p class="msg_ok">'.$ipsuccessfullyunignored.'</p>';
      unset($_POST);
      clearstatcache();
      sleep(1);
    }
  }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta meta name="viewport" content="width=device-width, initial-scale=1" charset="utf-8">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css" type="text/css">
    <title>Fail2Ban Webinterface</title>
    <div class="header" id="myHeader">
      <h1>Fail2Ban Webinterface</h1>
      <form name="reload" method="POST">
        <button class="button" type="submit" name="submit_reload"><?=$refresh?>
          <img src="images/reload.svg" alt="add">
        </button>
      </form>
    </div>
    <?php
      $available=available();
      if(!$available) {
        echo '<h1><p class="msg_er">'.$serviceerror.'</p></h1>';
        exit;
      }
    ?>
  </head>
  <body>
    <h2><?=$bannedclientsperJail?></h2>
    <?php
      $usedns=$_GET['usedns'];
      $jailnoempty=$_GET['jailnoempty'];
      $jailinfo=$_GET['jailinfo'];
      $showignored=$_GET['showignored'];
      if($usedns==1) {
        $usednsv="checked='checked'";
      } else {
        $usednsv=="";
      }
      if($jailnoempty==1) {
        $jailnoemptyv="checked='checked'";
      } else {
        $jailnoemptyv=="";
      }
      if($jailinfo==1) {
        $jailinfov="checked='checked'";
      } else {
        $jailinfov=="";
      }
      if($showignored==1) {
        $showignoredv="checked='checked'";
      } else {
        $showignoredv=="";
      }
    ?>
    <form name="settings" method="post">
      <table>
        <tr>
          <td align="right">
            <label for="usedns"><?=$usedns_txt?></label>
            <br><label for="jailnoempty"><?=$jailnoempty_txt?></label>
            <br><label for="jailinfo"><?=$jailinfo_txt?></label>
            <br><label for="showignored"><?=$showignored_txt?></label>
          </td>
          <td>
            <input type="checkbox" name="usedns" id="usedns" value="1" <?=$usednsv?>/>
            <br><input type="checkbox" name="jailnoempty" id="jailnoempty" value="1" <?=$jailnoemptyv?>/>
            <br><input type="checkbox" name="jailinfo" id="jailinfo" value="1" <?=$jailinfov?>/>
            <br><input type="checkbox" name="showignored" id="showignored" value="1" <?=$showignoredv?>/>
          </td>
          <td rowspan="4">
            <button class="button" type="submit" name="submit_settings"><?=$apply ?>
              <img src="images/apply.svg" alt="apply" title="<?=$apply ?>">
            </button>
          </td>
        </tr>
      </table>
    </form>
    <?=$error_unban==null?"&nbsp;":$error_unban?>
    <?php
      $jails=list_jails();
      foreach($jails as $jail=>$client_banned) {
        $clients_banned=list_clients_banned($jail,$usedns);
        $jails[$jail]=$clients_banned;
      }
    ?>
    <table>
      <?php
        foreach($jails as $jail=>$clients) {
          if($jailnoempty==1 || is_array($clients)) {
            echo '<thead><tr><td class="bold" colspan="2">'.strtoupper($jail);
            if($jailinfo==1) {
              $jail_info=jail_info($jail);
              $jail_info=implode(', ',$jail_info);
              echo '<span class="msg_gr"> >> '.$jail_info.'</span>';
            }
            echo '</td></tr></thead>';
            if(is_array($clients)) {
              foreach($clients as $client) {
                $client_ip=explode(" (", $client)[0];
                echo '
                  <tr class="highlight">
                    <form name="unban" method="POST">
                      <input type="hidden" name="unban_jail" value="'.$jail.'">
                      <input type="hidden" name="unban_ip" value="'.$client_ip.'">
                      <td align="right">'.$client.'</td>
                      <td align="center">
                        <button class="button" type="submit" name="submit_del">
                          <img src="images/del.svg" alt="del" title="'.$unbanip.' '.$client_ip.'">
                        </button>
                      </td>
                    </form>
                  </tr>
                ';
              }
            } else {
              echo '<tr class="highlight"><td class="msg_gr" colspan="2">'.$nobannedclients.'</td></tr>';
            }
          }
        }
      ?>
    </table>
    <h2><?=$manuallyaddbannedclienttoJail?></h2>
    <?=$error_ban==null?null:$error_ban?>
    <form name="ban" method="POST">
      <table>
        <tr>
          <th>Jail</th>
          <th>IP</th>
          <th><?=$banip?></th>
        </tr>
        <tr class="highlight">
          <td>
            <select name="ban_jail"><option value="">- <?=$select?> -</option>
              <?php
                foreach($jails as $jail=>$clients) {
                  echo '<option value="'.$jail.'"';
                  if($_POST['ban_jail']==$jail) {
                    echo ' selected';
                  }
                  echo '>'.$jail.'</option>';
                }
              ?>
            </select>
          </td>
          <td><input type="text" name="ban_ip" value="<?=$_POST['ban_ip']?>"></td>
          <td align="center">
            <button class="button" type="submit" name="submit_add">
              <img src="images/add.svg" alt="add" title="<?=$banip ?>">
            </button>
          </td>
        </tr>
      </table>
    </form>
    <?php if($showignored==1): ?>
    <h2><?=$whitelistedclientsperJail?></h2>
    <?=$error_unignore==null?"&nbsp;":$error_unignore?>
    <table>
      <?php
        foreach($jails as $jail=>$clients) {
          $ignored_ips=list_ignored_ips($jail);
          if($jailnoempty==1 || is_array($ignored_ips)) {
            echo '<thead><tr><td class="bold" colspan="2">'.strtoupper($jail).'</td></tr></thead>';
            if(is_array($ignored_ips)) {
              foreach($ignored_ips as $ignored_ip) {
                echo '
                  <tr class="highlight">
                    <form name="unignore" method="POST">
                      <input type="hidden" name="unignore_jail" value="'.$jail.'">
                      <input type="hidden" name="unignore_ip" value="'.htmlspecialchars($ignored_ip).'">
                      <td align="right">'.htmlspecialchars($ignored_ip).'</td>
                      <td align="center">
                        <button class="button" type="submit" name="submit_delignore">
                          <img src="images/del.svg" alt="del" title="'.$unignoreip.' '.htmlspecialchars($ignored_ip).'">
                        </button>
                      </td>
                    </form>
                  </tr>
                ';
              }
            } else {
              echo '<tr class="highlight"><td class="msg_gr" colspan="2">'.$noignoredclients.'</td></tr>';
            }
          }
        }
      ?>
    </table>
    <h2><?=$manuallyaddwhitelistedclienttoJail?></h2>
    <?=$error_ignore==null?null:$error_ignore?>
    <form name="ignore" method="POST">
      <table>
        <tr>
          <th>Jail</th>
          <th>IP</th>
          <th><?=$ignoreip?></th>
        </tr>
        <tr class="highlight">
          <td>
            <select name="ignore_jail"><option value="">- <?=$select?> -</option>
              <?php
                foreach($jails as $jail=>$clients) {
                  echo '<option value="'.$jail.'"';
                  if($_POST['ignore_jail']==$jail) {
                    echo ' selected';
                  }
                  echo '>'.$jail.'</option>';
                }
              ?>
            </select>
          </td>
          <td><input type="text" name="ignore_ip" value="<?=$_POST['ignore_ip']?>"></td>
          <td align="center">
            <button class="button" type="submit" name="submit_addignore">
              <img src="images/add.svg" alt="add" title="<?=$ignoreip ?>">
            </button>
          </td>
        </tr>
      </table>
    </form>
    <?php endif; ?>
    <br>
  </body>
  <footer>
    <hr>
    <?=date("r")?>
  </footer>
</html>
