<?
srand((float) microtime() * 10000000);

function a_array_rand($input, $num_req) {
  if(count($input) == 0) { return array(); }
  if($num_req < 1) { return array(); }
  $out = array();
  if($num_req > count($input)) {
    for($i = 0; $i < $num_req; $i++) {
      $idx = array_rand($input, 1);
      $out[] = $input[$idx];
    }
  } else {
    $idxlist = array_rand($input, $num_req);
    if($num_req == 1) { $idxlist = array($idxlist); }
    for($i = 0; $i < count($idxlist); $i++) {
      $out[] = $input[$idxlist[$i]];
    }
  }
  return $out;
}

function gpass($num, $lluse, $llnum, $luuse, $lunum, $nuse, $nnum, $puse, $pnum) {
  $outarray = array();

  $llower = explode(":", "a:b:c:d:e:f:g:h:k:m:n:p:q:r:s:t:u:v:w:x:y:z");
  shuffle($llower);
  $lupper = explode(":", "A:B:C:D:E:F:G:H:K:M:N:P:Q:R:S:T:U:V:W:X:Y:Z");
  shuffle($lupper);
  $numbers = explode(":", "2:3:4:5:6:7:8:9");
  shuffle($numbers);
  $punc = explode(":", "#:$:%:^:&:*:(:):_:-:+:=:.:,:[:]:{:}");
  shuffle($punc);

  if($lluse) {
    $outarray = array_merge($outarray, a_array_rand($llower, $llnum));
  }
  if($luuse) {
    $outarray = array_merge($outarray, a_array_rand($lupper, $lunum));
  }
  if($nuse) {
    $outarray = array_merge($outarray, a_array_rand($numbers, $nnum));
  }
  if($puse) {
    $outarray = array_merge($outarray, a_array_rand($punc, $pnum));
  }

  if(($llnum + $lunum + $nnum + $pnum) < $num) {
    $leftover = array();
    if($lluse) { $leftover = array_merge($leftover, $llower); }
    if($luuse) { $leftover = array_merge($leftover, $lupper); }
    if($nuse) { $leftover = array_merge($leftover, $numbers); }
    if($puse) { $leftover = array_merge($leftover, $punc); }
    if(count($leftover) == 0) {
      $leftover = array_merge($leftover, $llower, $lupper, $numbers, $punc);
    }
    shuffle($leftover);
    $outarray = array_merge($outarray, a_array_rand($leftover, $num - ($llnum + $lunum + $nnum + $pnum)));
  }
  shuffle($outarray);
  return implode('', $outarray);
}
?>
