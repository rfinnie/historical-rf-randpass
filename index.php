<?
$ver = 1.2;
include('smb.inc.php');
include('password.inc.php');

$has_sha1 = 0;
if(function_exists('sha1')) {
  $has_sha1 = 1;
} elseif(function_exists('mhash')) {
  function sha1($data) {
    return bin2hex(mhash(MHASH_SHA1, $data));
  }
  $has_sha1 = 1;
}
function crc16($string) {
  $crc = 0xFFFF;
  for ($x = 0; $x < strlen ($string); $x++) {
   $crc = $crc ^ ord($string[$x]);
   for ($y = 0; $y < 8; $y++) {
     if (($crc & 0x0001) == 0x0001) {
       $crc = (($crc >> 1) ^ 0xA001);
     } else { $crc = $crc >> 1; }
   }
  }
  return $crc;
}
?>
<html>
<head>
<title>RF Random Password Generator <? echo $ver; ?></title>
</head>
<body>
<h1>RF Random Password Generator <? echo $ver; ?></h1>
<?
if($_GET['submit']) {
  if($_GET['llnum'] < 0 || !$_GET['lluse']) { $_GET['llnum'] = 0; }
  if($_GET['lunum'] < 0 || !$_GET['luuse']) { $_GET['lunum'] = 0; }
  if($_GET['nnum'] < 0 || !$_GET['nuse']) { $_GET['nnum'] = 0; }
  if($_GET['pnum'] < 0 || !$_GET['puse']) { $_GET['pnum'] = 0; }
  ?>
  <p>The following passwords have been generated according to your specifications:</p>
  <ul>
    <?
    for($i = 0; $i < $_GET['qty']; $i++) {
      $pass = gpass($_GET['num'], $_GET['lluse'], $_GET['llnum'], $_GET['luuse'], $_GET['lunum'], $_GET['nuse'], $_GET['nnum'], $_GET['puse'], $_GET['pnum']);
      ?><li><tt><? echo $pass; ?></tt></li><?
      if($_GET['p'] && count($_GET['p'] > 0)) {
        ?><ul><?
        if($_GET['p']['base64']) {
          ?><li>Base64: <tt><? echo base64_encode($pass); ?></tt></li><?
        }
        if($_GET['p']['md5hash']) {
          ?><li>MD5 Hash (lowercase): <tt><? echo md5($pass); ?></tt></li><?
        }
        if($_GET['p']['md5hash_uc']) {
          ?><li>MD5 Hash (uppercase): <tt><? echo strtoupper(md5($pass)); ?></tt></li><?
        }
        if($_GET['p']['sha1hash']) {
          ?><li>SHA1 Hash (lowercase): <tt><? echo sha1($pass); ?></tt></li><?
        }
        if($_GET['p']['sha1hash_uc']) {
          ?><li>SHA1 Hash (uppercase): <tt><? echo strtoupper(sha1($pass)); ?></tt></li><?
        }
        if($_GET['p']['crc16hash']) {
          ?><li>CRC16 Hash: <tt><? printf('%u', crc16($pass)); ?></tt></li><?
        }
        if($_GET['p']['crc32hash']) {
          ?><li>CRC32 Hash: <tt><? printf('%u', crc32($pass)); ?></tt></li><?
        }
        if($_GET['p']['md5crypt']) {
          $jumble = md5(rand(1,100000000));
          $salt = substr($jumble,0,8);
          ?><li>MD5 Crypt: <tt><? echo crypt($pass, '$1$' . $salt); ?></tt></li><?
        }
        if($_GET['p']['descrypt']) {
          $jumble = base64_encode(md5(rand(1,100000000)));
          $salt = substr($jumble,0,2);
          ?><li>DES Crypt (Standard): <tt><? echo crypt($pass, $salt); ?></tt></li><?
        }
        if($_GET['p']['desextcrypt']) {
          $jumble = base64_encode(md5(rand(1,100000000)));
          $salt = substr($jumble,0,9);
          ?><li>DES Crypt (Extended): <tt><? echo crypt($pass, $salt); ?></tt></li><?
        }
        if($_GET['p']['blowcrypt']) {
          $jumble = md5(rand(1,100000000));
          $salt = substr($jumble,0,16);
          ?><li>Blowfish Crypt: <tt><? echo crypt($pass, '$2$' . $salt); ?></tt></li><?
        }
        if($_GET['p']['nt_hash'] && function_exists(mhash)) {
          ?><li>NT Hash: <tt><? echo nt_hash($pass); ?></tt></li><?
        }
        if($_GET['p']['lm_hash'] && function_exists(mcrypt_generic)) {
          ?><li>LM Hash: <tt><? echo lm_hash($pass); ?></tt></li><?
        }
        ?></ul><?
      }
    }
    ?>
  </ul>
  <?
} else {
  ?>
  <p>This program generates a list of random passwords based on the specifications below.  Note: similar looking characters will not be used to generate the passwords, such as 1 and l, 0 and O, etc.
  This program <? if($_SERVER['HTTPS']) { ?>is currently using SSL (<? echo $_SERVER['SSL_CIPHER']; ?> cipher) and <? } ?>does not save any of its randomly generated passwords.  However, you should not trust that, and should instead <a href="http://www.finnie.org/software/randpass/">download the source</a> yourself.</p>
  <form method="GET" action="<? echo $PHP_SELF; ?>">
  Number of passwords to generate: <input name="qty" size="2" value="1"><br>
  Password length: <input name="num" size="2" value="9"><br>
  <hr>
  Use lowercase letters: <input type="checkbox" name="lluse" checked> - Minimum required: <input name="llnum" size="2" value="2"><br>
  Use uppercase letters: <input type="checkbox" name="luuse" checked> - Minimum required: <input name="lunum" size="2" value="2"><br>
  Use numbers: <input type="checkbox" name="nuse" checked> - Minimum required: <input name="nnum" size="2" value="2"><br>
  Use punctuation: <input type="checkbox" name="puse"> - Minimum required: <input name="pnum" size="2" value="2"><br>
  <hr>
  Print Base64-encoded password: <input type="checkbox" name="p[base64]"><br>
  Print MD5-hashed password (lowercase): <input type="checkbox" name="p[md5hash]"><br>
  Print MD5-hashed password (uppercase): <input type="checkbox" name="p[md5hash_uc]"><br>
  <? if($has_sha1) { ?>Print SHA1-hashed password (lowercase): <input type="checkbox" name="p[sha1hash]"><br><? } ?>
  <? if($has_sha1) { ?>Print SHA1-hashed password (uppercase): <input type="checkbox" name="p[sha1hash_uc]"><br><? } ?>
  Print CRC16-hashed password: <input type="checkbox" name="p[crc16hash]"><br>
  Print CRC32-hashed password: <input type="checkbox" name="p[crc32hash]"><br>
  <? if(CRYPT_MD5 == 1) { ?>Print MD5-crypted password: <input type="checkbox" name="p[md5crypt]"><br><? } ?>
  <? if(CRYPT_STD_DES == 1) { ?>Print DES-crypted password (standard): <input type="checkbox" name="p[descrypt]"><br><? } ?>
  <? if(CRYPT_EXT_DES == 1) { ?>Print DES-crypted password (extended): <input type="checkbox" name="p[desextcrypt]"><br><? } ?>
  <? if(CRYPT_BLOWFISH == 1) { ?>Print Blowfish-crypted password: <input type="checkbox" name="p[blowcrypt]"><br><? } ?>
  <? if(function_exists(mhash)) { ?>Print NT hash: <input type="checkbox" name="p[nt_hash]"><br><? } ?>
  <? if(function_exists(mcrypt_generic)) { ?>Print LM hash: <input type="checkbox" name="p[lm_hash]"><br><? } ?>
  <hr>
  <input type="submit" name="submit" value="Generate Passwords">
  </form>
  <?
}
?>
</body>
</html>
