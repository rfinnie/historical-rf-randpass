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

function gpass($criteria) {
  $outarray = array();

  if($criteria['no_use_similar']) {
    $raw_lower = "a b c d e f g h k m n p q r s t u v w x y z";
    $raw_numbers = "2 3 4 5 6 7 8 9";
    $raw_punc = "# $ % ^ & * ( ) _ - + = . , [ ] { } :";
  } else {
    $raw_lower = "a b c d e f g h i j k l m n o p q r s t u v w x y z";
    $raw_numbers = "1 2 3 4 5 6 7 8 9 0";
    $raw_punc = "# $ % ^ & * ( ) _ - + = . , [ ] { } : |";
  }
  $llower = explode(" ", $raw_lower);
  shuffle($llower);
  $lupper = explode(" ", strtoupper($raw_lower));
  shuffle($lupper);
  $numbers = explode(" ", $raw_numbers);
  shuffle($numbers);
  $punc = explode(" ", $raw_punc);
  shuffle($punc);

  if($criteria['lowercase'] > 0) {
    $outarray = array_merge($outarray, a_array_rand($llower, $criteria['lowercase']));
  }
  if($criteria['uppercase'] > 0) {
    $outarray = array_merge($outarray, a_array_rand($lupper, $criteria['uppercase']));
  }
  if($criteria['numbers'] > 0) {
    $outarray = array_merge($outarray, a_array_rand($numbers, $criteria['numbers']));
  }
  if($criteria['punctuation'] > 0) {
    $outarray = array_merge($outarray, a_array_rand($punc, $criteria['punctuation']));
  }

  $num_spec = $criteria['lowercase'] + $criteria['uppercase'] + $criteria['numbers'] + $criteria['punctuation'];
  if($num_spec < $criteria['num']) {
    $leftover = array();
    if($criteria['lowercase'] > 0) { $leftover = array_merge($leftover, $llower); }
    if($criteria['uppercase'] > 0) { $leftover = array_merge($leftover, $lupper); }
    if($criteria['numbers'] > 0) { $leftover = array_merge($leftover, $numbers); }
    if($criteria['punctuation'] > 0) { $leftover = array_merge($leftover, $punc); }
    if(count($leftover) == 0) {
      $leftover = array_merge($leftover, $llower, $lupper, $numbers, $punc);
    }
    shuffle($leftover);
    $outarray = array_merge($outarray, a_array_rand($leftover, $criteria['num'] - $num_spec));
  }
  shuffle($outarray);
  return implode('', $outarray);
}
?>
