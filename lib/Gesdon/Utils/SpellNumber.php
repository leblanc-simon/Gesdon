<?php

namespace Gesdon\Utils;

// issu de http://bits.jaws-project.com/ sous GNU/GPL
// modification pour adaptation français par Simon Leblanc

//Digits
define('SPELLNUMBER_SEPARATOR', "");
define('SPELLNUMBER_SEPARATOR_1', "et");
define('SPELLNUMBER_0', "zero");
define('SPELLNUMBER_1', "un");
define('SPELLNUMBER_2', "deux");
define('SPELLNUMBER_3', "trois");
define('SPELLNUMBER_4', "quatre");
define('SPELLNUMBER_5', "cinq");
define('SPELLNUMBER_6', "six");
define('SPELLNUMBER_7', "sept");
define('SPELLNUMBER_8', "huit");
define('SPELLNUMBER_9', "neuf");
define('SPELLNUMBER_10', "dix");
define('SPELLNUMBER_11', "onze");
define('SPELLNUMBER_12', "douze");
define('SPELLNUMBER_13', "treize");
define('SPELLNUMBER_14', "quatorze");
define('SPELLNUMBER_15', "quinze");
define('SPELLNUMBER_16', "seize");
define('SPELLNUMBER_17', "dix-sept");
define('SPELLNUMBER_18', "dix-huit");
define('SPELLNUMBER_19', "dix-neuf");
define('SPELLNUMBER_20', "vingt");
define('SPELLNUMBER_30', "trente");
define('SPELLNUMBER_40', "quarante");
define('SPELLNUMBER_50', "cinquante");
define('SPELLNUMBER_60', "soixante");
define('SPELLNUMBER_70', "soixante dix");
define('SPELLNUMBER_71', "soixante et onze");
define('SPELLNUMBER_72', "soixante douze");
define('SPELLNUMBER_73', "soixante treize");
define('SPELLNUMBER_74', "soixante quatorze");
define('SPELLNUMBER_75', "soixante quinze");
define('SPELLNUMBER_76', "soixante seize");
define('SPELLNUMBER_77', "soixante dix-sept");
define('SPELLNUMBER_78', "soixante dix-huit");
define('SPELLNUMBER_79', "soixante dix-neuf");
define('SPELLNUMBER_80', "quatre-vingt");
define('SPELLNUMBER_90', "quatre-vingt dix");
define('SPELLNUMBER_91', "quatre-vingt onze");
define('SPELLNUMBER_92', "quatre-vingt douze");
define('SPELLNUMBER_93', "quatre-vingt treize");
define('SPELLNUMBER_94', "quatre-vingt quatorze");
define('SPELLNUMBER_95', "quatre-vingt quinze");
define('SPELLNUMBER_96', "quatre-vingt seize");
define('SPELLNUMBER_97', "quatre-vingt dix-sept");
define('SPELLNUMBER_98', "quatre-vingt dix-huit");
define('SPELLNUMBER_99', "quatre-vingt dix-neuf");
define('SPELLNUMBER_100', "cent");
define('SPELLNUMBER_200', "deux cent");
define('SPELLNUMBER_300', "trois cent");
define('SPELLNUMBER_400', "quatre cent");
define('SPELLNUMBER_500', "cinq cent");
define('SPELLNUMBER_600', "six cent");
define('SPELLNUMBER_700', "sept cent");
define('SPELLNUMBER_800', "huit cent");
define('SPELLNUMBER_900', "neuf cent");
define('SPELLNUMBER_1000', "mille");
define('SPELLNUMBER_1000000', "million");
define('SPELLNUMBER_1000000000', "milliard");
define('SPELLNUMBER_1000000000000', "trillion");


class SpellNumber
{
    /**
     * NumberToText
     *
     * @access  public
     * @param   string  $num number to parse
     * @return  string
     */
    public static function NumberToText($num)
    {
        $ret_str = '';
        while ($num !='' ) {
            $part = (int)((strlen($num)-1)/3);
            $sub_len = strlen($num) - $part*3;
            if ($sub_len  == 0) $sub_len = 3;
            $sub_num = substr($num, 0, $sub_len);
            $num = substr($num, $sub_len);

            $ret_sub_str = '';
            while ($sub_num != '') {
                while (($sub_num != '') && ($sub_num[0] == '0')) {
                    if (strlen($sub_num) == 1) {
                        $sub_num = '';
                    } else {
                        $sub_num = substr($sub_num, 1);
                    }
                }
                $sub_len = strlen($sub_num);
                switch ($sub_len) {
                case 3:
                    $ret_sub_str = SpellNumber::DigitName($sub_num[0], 2);
                    $sub_num = substr($sub_num, 1);
                    break;
                case 2:
                    if ($sub_num[0] == '1' || $sub_num[0] == '7' || $sub_num[0] == '9') {
                        $ret_sub_str = $ret_sub_str.
                                       (($ret_sub_str=='')?'':(SPELLNUMBER_SEPARATOR.' ')).
                                       SpellNumber::TwoDigitName($sub_num);
                        $sub_num = '';
                    } else  {
                        $ret_sub_str = $ret_sub_str.
                                       (($ret_sub_str=='')?'':(SPELLNUMBER_SEPARATOR.' ')).
                                       SpellNumber::DigitName($sub_num[0], 1);
                        //si c'est une dizaine et que l'unité et un "un", on ajoute "et"
                        if ($sub_num[1] == '1') {
                            $ret_sub_str .= ' '.SPELLNUMBER_SEPARATOR_1;
                        }
                        $sub_num = substr($sub_num, 1);
                    }
                    break;
                case 1:
                    if ($sub_num == '1' && $part == 1) { // on gère le cas du mille (on ne dit pas un mille)
                        $ret_sub_str = $ret_sub_str.
                                   (($ret_sub_str=='')?'':(SPELLNUMBER_SEPARATOR.' '));
                        $french_un = true;
                    } else {
                        $ret_sub_str = $ret_sub_str.
                                   (($ret_sub_str=='')?'':(SPELLNUMBER_SEPARATOR.' ')).
                                   SpellNumber::DigitName($sub_num, 0);
                    }
                    $sub_num = '';
                    break;
                default:
                    $sub_num = '';
                }
            }
            $ret_str = $ret_str.
                       ((($ret_sub_str!='') && ($ret_str != ''))?(SPELLNUMBER_SEPARATOR.' ') : '').
                       $ret_sub_str.
                       (($ret_sub_str=='' && $french_un = false) ? '' : (' ' . SpellNumber::GroupName($part)));
            
            $french_un = false;
        }
        if ($ret_str=='') {
            $ret_str = SPELLNUMBER_0;
        }

        return $ret_str;
    }
    
    /**
     * @access  public
     * @param   int
     * @return  string
     */
    public static function GroupName($g_num)
    {
        if ($g_num == 0) {
            return '';
        }

        $g_str = '';
        while ($g_num > 0) {
            $g_str = $g_str . '000';
            $g_num = $g_num - 1;
        }
        $g_str = 'SPELLNUMBER_1'.$g_str;

        return constant($g_str);
    }

    /**
     * @access  public
     * @param   int $digit
     * @param   int $order
     * @return  string
     */
    public static function DigitName($digit, $order)
    {
        $d_str = 'SPELLNUMBER_' . $digit;
        while ($order > 0) {
            $d_str = $d_str.'0';
            $order = $order - 1;
        }

        return constant($d_str);
    }
    
    /**
     * @access  public
     * @param   int
     * @return  string
     */
    public static function TwoDigitName($digits)
    {
        $td_str = 'SPELLNUMBER_' . $digits;
        return constant($td_str);
    }
}