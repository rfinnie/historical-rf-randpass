<?
$ver = "1.3+dev";
include_once('smb.inc.php');
include_once('password.inc.php');

# The maximum number of passwords to generate.  If the user asks for more,
# the program will lower it to this number and warn the user.
$qty_limit = 100;
# The maximum length of any generated password.  If the user asks for more,
# the program will lower it to this number and warn the user.
$num_limit = 32;

# The cracklib dictionary can be in several places.
$cracklib_dict = '/usr/lib/cracklib_dict';

// PHP4 <4.1.0
if(!$_REQUEST) {
  $_REQUEST = array_merge($HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS);
}

if(function_exists('mhash')) {
  $has_mhash = 1;
  $mhashlist = array();
  foreach(get_defined_constants() as $name => $val) {
    if(substr($name, 0, 6) == 'MHASH_') {
      $mhashlist[] = strtolower(substr($name, 6));
    }
  }
  sort($mhashlist);
}

if(function_exists('hash')) {
  $has_hash = 1;
  $hashlist = hash_algos();
  sort($hashlist);
}

# If PHP < 4.3.0 is installed AND mhash or hash is installed, emulate sha1().
# If neither are available, don't include SHA1 functionality.
$has_sha1 = 0;
if(function_exists('sha1')) {
  $has_sha1 = 1;
} elseif(function_exists('hash')) {
  function sha1($data) {
    return hash('sha1', $data);
  }
  $has_sha1 = 1;
} elseif(function_exists('mhash')) {
  function sha1($data) {
    return bin2hex(mhash(MHASH_SHA1, $data));
  }
  $has_sha1 = 1;
}

# I doubt anyone needs CRC16 support, but here it is...
# Ported to PHP from original C public domain code.
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
if($_REQUEST['submit']) {
  $warnings = array();

  # Input sanitizing.  It's not elegant, but it works.
  foreach(array('llnum', 'lunum', 'nnum', 'pnum', 'qty', 'num') as $i) {
    $_REQUEST[$i] = abs($_REQUEST[$i] + 0);
  }

  # If the quantity of number of characters are above the coded limits, lower
  # them to the respective limits.  If the sum of all requested character
  # types are more than the character limit, lower each character type down
  # linearly until the sum is at or slightly below the character limit.
  if($_REQUEST['qty'] > $qty_limit) {
    $_REQUEST['qty'] = $qty_limit;
    $warnings[] = "The number of generated passwords has been truncated to $qty_limit.";
  }
  if($_REQUEST['num'] > $num_limit) {
    $_REQUEST['num'] = $num_limit;
    $warnings[] = "The length of each generated password has been truncated to $num_limit characters.";
  }
  if(($_REQUEST['llnum'] + $_REQUEST['lunum'] + $_REQUEST['nnum'] + $_REQUEST['pnum']) > $num_limit) {
    $fudge = $num_limit / ($_REQUEST['llnum'] + $_REQUEST['lunum'] + $_REQUEST['nnum'] + $_REQUEST['pnum']);
    $_REQUEST['llnum'] = intval($_REQUEST['llnum'] * $fudge);
    $_REQUEST['lunum'] = intval($_REQUEST['lunum'] * $fudge);
    $_REQUEST['nnum'] = intval($_REQUEST['nnum'] * $fudge);
    $_REQUEST['pnum'] = intval($_REQUEST['pnum'] * $fudge);
    $warnings[] = "Since the sum of all specified minimum requirements is greater than the system limit ($num_limit), all requirements have been linearly lowered until the sum is below the limit.";
  }

  $totalreq = $_REQUEST['llnum'] + $_REQUEST['lunum'] + $_REQUEST['nnum'] + $_REQUEST['pnum'];
  if($totalreq > $_REQUEST['num']) {
    $rnum = $_REQUEST['num'];
    $warnings[] = "The sum of the minimum requirements ($totalreq) is greater than the specified password length ($rnum).  Therefore, generated passwords will be $totalreq characters, instead of $rnum.";
  }

  ?>
  <p>The following passwords have been generated according to your specifications:</p>
  <ul>
    <?
    for($i = 0; $i < $_REQUEST['qty']; $i++) {
      # The library expects an associative array of options.
      # Please see the library code for an explanation of options.
      $criteria = array(
        'num' => $_REQUEST['num'],
        'lowercase' => $_REQUEST['llnum'],
        'uppercase' => $_REQUEST['lunum'],
        'numbers' => $_REQUEST['nnum'],
        'punctuation' => $_REQUEST['pnum'],
        'no_use_similar' => $_REQUEST['nosuse']
      );
      $pass = gpass($criteria);
      ?><li><tt><? echo $pass; ?></tt></li><?
      if($_REQUEST['p'] && count($_REQUEST['p']) > 0) {
        ?><ul><?
        if($_REQUEST['p']['base64']) {
          ?><li>Base64: <tt><? echo base64_encode($pass); ?></tt></li><?
        }
        if($_REQUEST['p']['md5hash']) {
          $hash = md5($pass);
          ?><li>MD5 Hash: <tt><? echo $hash; ?></tt></li><?
        }
        if($_REQUEST['p']['md4hash']) {
          $hash = bin2hex(mhash(MHASH_MD4, $pass));
          ?><li>MD4 Hash: <tt><? echo $hash; ?></tt></li><?
        }
        if($_REQUEST['p']['sha1hash']) {
          $hash = sha1($pass);
          ?><li>SHA1 Hash: <tt><? echo $hash; ?></tt></li><?
        }
        if($_REQUEST['p']['crc16hash']) {
          ?><li>CRC16 Hash: <tt><? printf('%u', crc16($pass)); ?></tt></li><?
        }
        if($_REQUEST['p']['crc32hash']) {
          ?><li>CRC32 Hash: <tt><? printf('%u', crc32($pass)); ?></tt></li><?
        }
        if($_REQUEST['p']['md5crypt']) {
          $jumble = md5(rand(1,100000000));
          $salt = substr($jumble,0,8);
          ?><li>MD5 Crypt: <tt><? echo crypt($pass, '$1$' . $salt); ?></tt></li><?
        }
        if($_REQUEST['p']['descrypt']) {
          $jumble = base64_encode(md5(rand(1,100000000)));
          $salt = substr($jumble,0,2);
          ?><li>DES Crypt (Standard): <tt><? echo crypt($pass, $salt); ?></tt></li><?
        }
        if($_REQUEST['p']['desextcrypt']) {
          $jumble = base64_encode(md5(rand(1,100000000)));
          $salt = substr($jumble,0,9);
          ?><li>DES Crypt (Extended): <tt><? echo crypt($pass, $salt); ?></tt></li><?
        }
        if($_REQUEST['p']['blowcrypt']) {
          $jumble = md5(rand(1,100000000));
          $salt = substr($jumble,0,16);
          ?><li>Blowfish Crypt: <tt><? echo crypt($pass, '$2$' . $salt); ?></tt></li><?
        }
        if($_REQUEST['p']['nt_hash'] && function_exists(mhash)) {
          ?><li>NT Hash: <tt><? echo nt_hash($pass); ?></tt></li><?
        }
        if($_REQUEST['p']['lm_hash'] && function_exists(mcrypt_generic)) {
          ?><li>LM Hash: <tt><? echo lm_hash($pass); ?></tt></li><?
        }
        if($_REQUEST['p']['hash_hashes'] && (count($_REQUEST['p']['hash_hashes']) > 0)) {
          ?><li>"hash" hashes:<ul><?
          foreach($_REQUEST['p']['hash_hashes'] as $hashtype) {
            $hash = hash($hashtype, $pass);
            ?><li><? echo $hashtype; ?>: <tt><? echo $hash; ?></tt></li><?
          }
          ?></ul></li><?
        }
        if($_REQUEST['p']['mhash_hashes'] && (count($_REQUEST['p']['mhash_hashes']) > 0) && function_exists('constant')) {
          ?><li>"mhash" hashes:<ul><?
          foreach($_REQUEST['p']['mhash_hashes'] as $hashtype) {
            $hash = bin2hex(mhash(constant('MHASH_' . strtoupper($hashtype)), $pass));
            ?><li><? echo $hashtype; ?>: <tt><? echo $hash; ?></tt></li><?
          }
          ?></ul></li><?
        }
        if($_REQUEST['p']['cracklib'] && function_exists(crack_check) && $cracklib_dict) {
          $dict = crack_opendict($cracklib_dict);
          $check = crack_check($pass);
          $message = crack_getlastmessage();
          $success = ($check ? 'success' : 'failure');
          crack_closedict($dict);
          ?><li>Cracklib reports <? echo $success; ?>: <tt><? echo $message; ?></tt></li><?
        }
        ?></ul><?
      }
    }
    ?>
  </ul>
  <?
  if(count($warnings) > 0) {
    ?>
    <p>One or more warnings have been issued:</p>
    <ul>
    <?
    foreach($warnings as $warning) {
      ?>
      <li><? echo $warning; ?></li>
      <?
    }
    ?>
    </ul>
    <?
  }
} else {
  ?>
  <p>This program generates a list of random passwords based on the specifications below.
  This program <? if($_SERVER['HTTPS']) { ?>is currently using SSL (<? echo $_SERVER['SSL_CIPHER']; ?> cipher) and <? } ?>does not save any of its randomly generated passwords.  However, you should not trust that, and should instead <a href="http://www.finnie.org/software/randpass/">download the source</a> yourself.</p>
  <form method="POST" action="<? echo $PHP_SELF; ?>">
  Number of passwords to generate: <input name="qty" size="2" value="1"><br>
  Password length: <input name="num" size="2" value="9"><br>
  <hr>
  Minimum required lowercase letters: <input name="llnum" size="2" value="2"><br>
  Minimum required uppercase letters: <input name="lunum" size="2" value="2"><br>
  Minimum required numbers: <input name="nnum" size="2" value="2"><br>
  Minimum required punctuation: <input name="pnum" size="2" value="0"><br>
  Do not use similar characters (1/l, 0/O, etc): <input type="checkbox" name="nosuse" checked><br>
  <hr>
  Print Base64: <input type="checkbox" name="p[base64]"><br>
  Print MD5 hash: <input type="checkbox" name="p[md5hash]"><br>
  <? if(function_exists(mhash)) { ?>Print MD4 hash: <input type="checkbox" name="p[md4hash]"><br><? } ?>
  <? if($has_sha1) { ?>Print SHA1 hash: <input type="checkbox" name="p[sha1hash]"><br><? } ?>
  Print CRC16 hash: <input type="checkbox" name="p[crc16hash]"><br>
  Print CRC32 hash: <input type="checkbox" name="p[crc32hash]"><br>
  <? if(CRYPT_MD5 == 1) { ?>Print MD5 crypt: <input type="checkbox" name="p[md5crypt]"><br><? } ?>
  <? if(CRYPT_STD_DES == 1) { ?>Print DES crypt (standard): <input type="checkbox" name="p[descrypt]"><br><? } ?>
  <? if(CRYPT_EXT_DES == 1) { ?>Print DES crypt (extended): <input type="checkbox" name="p[desextcrypt]"><br><? } ?>
  <? if(CRYPT_BLOWFISH == 1) { ?>Print Blowfish crypt: <input type="checkbox" name="p[blowcrypt]"><br><? } ?>
  <? if(function_exists(mhash)) { ?>Print NT hash: <input type="checkbox" name="p[nt_hash]"><br><? } ?>
  <? if(function_exists(mcrypt_generic)) { ?>Print LM hash: <input type="checkbox" name="p[lm_hash]"><br><? } ?>
  <? if(function_exists(mhash) && function_exists('constant')) { ?>Print "mhash" hashes: <select name="p[mhash_hashes][]" multiple size="5"><? foreach($mhashlist as $i) { ?><option value="<? echo $i; ?>"><? echo $i; ?></option><? } ?></select><br><? } ?>
  <? if(function_exists(hash)) { ?>Print "hash" hashes: <select name="p[hash_hashes][]" multiple size="5"><? foreach($hashlist as $i) { ?><option value="<? echo $i; ?>"><? echo $i; ?></option><? } ?></select><br><? } ?>
  <? if(function_exists(crack_check) && $cracklib_dict) { ?>Check against cracklib: <input type="checkbox" name="p[cracklib]"><br><? } ?>
  <hr>
  <input type="submit" name="submit" value="Generate Passwords">
  </form>
  <?
}
?>
</body>
</html>
