<?php

 /* 
 * encoding ASCII to 7bit strings
 * @param string $text
 * @return string
 */
function encode7bit($text)
{
    $ret = '';
    $data = str_split($text);
    $mask = 0xFF;
    $shift = 0;
    $len = count($data);
    for ($i = 0; $i < $len; $i++) {
            $char = ord($data[$i]) & 0x7F; // only 7bits
            $nextChar = ($i+1 < $len) ? (ord($data[$i+1]) & 0x7F) : 0; // only 7bits
            if ($shift == 7) { $shift = 0; continue; }
            $carry  = ($nextChar & ((($mask << ($shift+1)) ^ 0xFF) & 0xFF));
            $digit = (($carry << (7-$shift)) | ($char >> $shift) ) & 0xFF;
            $ret .= chr($digit);
            $shift++;
    }
    $str = unpack('H*', $ret);
    return strtoupper($str[1]);
}
/**
 * decoding 7bit strings to ASCII
 * @param string $text
 * @return string
 */
function decode7bit($text)
{
    $ret = '';
    $data = str_split(pack('H*', $text));

    $mask = 0xFF;
    $shift = 0;
    $carry = 0;
    foreach ($data as $char) {
            if ($shift == 7) {
                    $ret .= chr($carry);
                    $carry = 0;
                    $shift = 0;
            }

            $a      =       ($mask >> ($shift+1)) & 0xFF;
            $b      =       $a ^ 0xFF;

            $digit = ($carry) | ((ord($char) & $a) << ($shift)) & 0xFF;
            $carry = (ord($char) & $b) >> (7-$shift);
            $ret .= chr($digit);

            $shift++;
    }
    if ($carry) $ret .= chr($carry);
    return $ret;
}
$strg = "*100*1#";
echo encode7bit($strg);
//echo decode7bit($strg);
?>
