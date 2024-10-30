<?php
/*
Plugin Name: Kreditkarten Validierung
Plugin URI: http://www.kreditkarte.org/
Description: Mit dem Plugin Kreditkarten Validierung ist es m&ouml;glich Kreditkartennummern auf ihre G&uuml;ltigkeit zu &uuml;berpr&uuml;fen. Einfach Integration in einen bestehenden Blog m&ouml;glich. Support: http://www.kreditkarte.org/
Version: 1.0
Author: Stefan Spirkl
Author URI: http://www.kreditkarte.org/
License: GPL3
*/

function Kreditkarten_Validierung($content)
{
    if (strpos($content, "<!-- kreditkartenvalidierung -->") !== false) {
        $content = preg_replace('/<p>\s*<!--(.*)-->\s*<\/p>/i', "<!--$1-->", $content);
        $content = str_replace('<!-- kreditkartenvalidierung -->', create_kreditkartenvalidierung(), $content);
    }
    return $content;
}

/* 
   * Create Kreditkarten Validierung
*/
function create_kreditkartenvalidierung()
{
    $ccnumber = $_POST['credit_card_number'];
    $out = '<form action="' . get_permalink() . '" method="post">
  <label for="nachname">Kreditkartennummer:</label>
  <br />
  <input name="credit_card_number" type="text" size="25" id="credit_card_number" value="' . $ccnumber . '"/>
  <br />
  <input type="submit" value="Kreditkarte Validierung" />
</form>';

    if ($ccnumber != "") {
        list($type, $valid) = validateCC($number);
        if ($valid) { 
            // Do something fun with the card
            echo "Number: <b>$ccnumber</b> ... Type: <b>$type</b> ... Valid: <b>$valid</b> <br />";
        }else { 
            // Return some sort of error
        }
    }
    return $out;
}

add_filter('the_content', 'Kreditkarten_Validierung');

function validateCC($ccnum)
{ 
    // Clean up input
    $ccnum = ereg_replace('[-[:space:]]', '', $ccnum); 
    // What kind of card do we have
    $type = check_type($ccnum); 
    // Does the number matchup ?
    $valid = check_number($ccnum);

    return array($type, $valid);
}
// Prefix and Length checks
function check_type($cardnumber)
{
    $cardtype = "UNKNOWN";

    $len = strlen($cardnumber);
    if ($len == 15 && substr($cardnumber, 0, 1) == '3') {
        $cardtype = "amex";
    }elseif ($len == 16 && substr($cardnumber, 0, 4) == '6011') {
        $cardtype = "discover";
    }elseif ($len == 16 && substr($cardnumber, 0, 1) == '5') {
        $cardtype = "mc";
    }elseif (($len == 16 || $len == 13) && substr($cardnumber, 0, 1) == '4') {
        $cardtype = "visa";
    }

    return ($cardtype);
}
// MOD 10 checks
function check_number($cardnumber)
{
    $dig = toCharArray($cardnumber);
    $numdig = sizeof ($dig);
    $j = 0;
    for ($i = ($numdig - 2); $i >= 0; $i -= 2) {
        $dbl[$j] = $dig[$i] * 2;
        $j++;
    }
    $dblsz = sizeof($dbl);
    $validate = 0;
    for ($i = 0;$i < $dblsz;$i++) {
        $add = toCharArray($dbl[$i]);
        for ($j = 0;$j < sizeof($add);$j++) {
            $validate += $add[$j];
        }
        $add = '';
    }
    for ($i = ($numdig - 1); $i >= 0; $i -= 2) {
        $validate += $dig[$i];
    }
    if (substr($validate, - 1, 1) == '0') {
        return 1;
    }else {
        return 0;
    }
} 
// takes a string and returns an array of characters
function toCharArray($input)
{
    $len = strlen($input);
    for ($j = 0;$j < $len;$j++) {
        $char[$j] = substr($input, $j, 1);
    }
    return ($char);
}

?>