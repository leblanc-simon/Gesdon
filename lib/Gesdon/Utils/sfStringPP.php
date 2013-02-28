<?php

namespace Gesdon\Utils;


/**
* Class to manipulate the content of your application
*
* Cette classe est une dérivée de la classe SLStringPeer
*
* @author Leblanc Simon <contact@leblanc-simon.eu>
* @license Licence MIT <http://www.opensource.org/licenses/mit-license.php>
* @version 1.3.0
*/
class sfStringPP
{
  const ENCODING_UTF8 = 'utf-8';
  const ENCODING_ISO = 'iso-8859-1';
  const ENCODING_ISO_15 = 'iso-8859-15';
  
  protected static $map = null;
  
  
  /**
* Détecter l'encodage de la chaine de caractère (ISO ou UTF-8)
*
* @param string $string La chaine à tester
* @return string L'encodage de la chaine ou null s'il n'est pas déterminé
* @access public
* @static
* @todo LA METHODE NECESSITE LA FONCTION ICONV !!!
*/
  public static function detectEncoding($string)
  {
    // La fonction nécessite iconv, s'il n'est pas présent on renvoie null
    if(function_exists('iconv') === false){
      return null;
    }
    
    static $list = array(self::ENCODING_UTF8, self::ENCODING_ISO_15, self::ENCODING_ISO);
    
    foreach($list as $item){
      $sample = @iconv($item, $item, $string);
      
      if (md5($sample) == md5($string)){
        return $item;
      }
    }
    
    return null;
  }
  
  
  /**
* Vérifie si la chaine de caractère est au format UTF-8
*
* @param string $string La chaine à tester
* @return bool Vrai si la chaine est en UTF-8, Faux sinon
* @access public
* @static
* @see detectEncoding
*/
  public static function isUtf8($string)
  {
    $res = self::detectEncoding($string);
    
    return (bool)($res === self::ENCODING_UTF8);
  }
  
  
  /**
* Convert a string in upper camel case mode
*
* @param string $string the string to convert
* @return string the string converted in upper camel case mode
* @access public
* @static
*/
  public static function upperCamelCase($string)
  {
    $string = self::noAccent($string);
    
    return ucwords(preg_replace("/(\_(.))/e", "self::toUpper('\\2')", self::toLower($string)));
  }
  
  
  /**
* Convert a string in upper camel case to underscore mode
*
* @param string $string the string to convert
* @return string the string converted in underscore mode
* @access public
* @static
*/
  public static function revertUpperCamelCase($string)
  {
    if(empty($string) === true){
      return $string;
    }
    
    if(strlen($string) > 1) {
      $string = self::toLower(substr($string, 0, 1)).substr($string, 1);
    }
    
    $string = self::noAccent($string);
    
    return preg_replace("/([A-Z])/e", "'_'.self::toLower('\\1')", $string);
  }
  
  
  
  /**
* Transforme le nom d'un champ de base de données en fonction PHP
* @param string $field le nom du champ à transformer
* @return string le nom du champ transformé en PHP
* @static
* @access protected
* @since 1.3.0
*/
  protected static function fieldToPhp($field)
  {
    return self::upperCamelCase($field);
  }
  
  
  /**
* Retourne le nom de la fonction getter PHP du champ de position
* @return string le nom de la fonction getter PHP
* @static
* @access public
* @since 1.3.0
*/
  public static function fieldToGetPhp($field)
  {
    return 'get'.self::fieldToPhp($field);
  }
  
  
  /**
* Retourne le nom de la fonction setter PHP du champ de position
* @return string le nom de la fonction setter PHP
* @static
* @access public
* @since 1.3.0
*/
  public static function fieldToSetPhp($field)
  {
    return 'set'.self::fieldToPhp($field);
  }
  
  
  /**
* Convert the string with HTML entities to string without HTML entities for accent characters
*
* @param String $content The string to convert
* @param Boolean $without_accent True : The HTML entities must be convert to characters without accent, False else
* @return String The string converted
* @access public
* @static
*/
  public static function entitiesToChar($content, $without_accent = false)
  {
    $map = self::getMap();
    
    // Create unicode entities
    $unicode_entities = array_merge($map['unicode_lower'], $map['unicode_upper']);
    array_walk($unicode_entities, 'sfStringPP::addUnicodeEntities');
    
    // Create html entities
    $html_entities = array_merge($map['html_lower'], $map['html_upper']);
    array_walk($html_entities, 'sfStringPP::addHtmlEntities');
    
    // Create character
    if($without_accent === false){
      $caract = array_merge($map['accent_lower'], $map['accent_upper']);
    } else {
      $caract = array_merge($map['no_accent_lower'], $map['no_accent_upper']);
    }
    
    // Convert unicode to character
    $content = str_replace($unicode_entities, $caract, $content);
    
    // Convert html to character
    $content = str_replace($html_entities, $caract, $content);
    
    // return the new content
    return $content;
  }
  
  
  /**
* Convert the string with accent characters to string HTML entities
*
* @param String $content The string to convert
* @return String The string converted
* @access public
* @static
*/
  public static function charToEntities($content)
  {
    $map = self::getMap();
    
    // Create unicode entities
    $unicode_entities = array_merge($map['unicode_lower'], $map['unicode_upper']);
    array_walk($unicode_entities, 'sfStringPP::addUnicodeEntities');
    
    // Create html entities
    $html_entities = array_merge($map['html_lower'], $map['html_upper']);
    array_walk($html_entities, 'sfStringPP::addHtmlEntities');
    
    // Create character
    $caract = array_merge($map['accent_lower'], $map['accent_upper']);
    
    // Convert character to unicode
    $content = str_replace($caract, $unicode_entities, $content);
    
    // Convert character to html
    $content = str_replace($caract, $html_entities, $content);
    
    // return the new content
    return $content;
  }
  
  
  /**
* Convert a string with accent to a string without accent
*
* @param String $string The string to convert
* @return String The string converting
* @access public
* @static
*/
  public static function noAccent($string)
  {
    $map = self::getMap();
    
    $accent = array_merge($map['accent_lower'], $map['accent_upper']);
    $no_accent = array_merge($map['no_accent_lower'], $map['no_accent_upper']);
    return str_replace($accent, $no_accent, $string);
  }
  
  
  /**
* Delete all specials characters of a string
*
* @param String $string The string to modified
* @param String $replace The characters for replace the specials characters
* @return String The string modified
* @access public
* @static
*/
  public static function noSpecialCharacters($string, $replace = '')
  {
    $specials_characters = array('"', '&', '€', '‚', 'ƒ', '„', '…', '†', '‡', 'ˆ', '‰', '<', '‘', '’',
                                 '“', '”', '•', '–', '—', '˜', '™', '›', '¡', '¢', '£', '¤', '¥', '¦',
                                 '§', '¨', '©', 'ª', '«', '¬', '­', '®', '¯', '°', '±', '²', '³', '´',
                                 'µ', '¶', '·', '¸', '¹', 'º', '»', '¼', '½', '¾', '¿', '/', '\\', '+',
                                 '*', ';', '!', '?', ',', '.', ':', '%', 'µ', '^', '¨', '}', ')', '@',
                                 '_', '`', '|', '(', '{', '#', '~', '[', ']', '=', ' ', "'", "\r\n",
                                 "\r", "\n", "\t");
    
    return str_replace($specials_characters, $replace, $string);
  }
  
  
  /**
* Convert a string to lowercase (compatible utf-8 not like strtoupper)
*
* @param String $string The string to convert
* @return String The string converting
* @access public
* @static
*/
  public static function toLower($string)
  {
    return mb_strtolower($string, mb_detect_encoding($string));
  }
  
  
  /**
* Convert a string to uppercase (compatible utf-8 not like strtoupper)
*
* @param String $string The string to convert
* @return String The string converting
* @access public
* @static
*/
  public static function toUpper($string)
  {
    return mb_strtoupper($string, mb_detect_encoding($string));
  }
  
  
  /**
* transforme une chaine de caractères en chaine utilisable pour un label
* @param String $string La chaine a transformer
* @return String La chaine transformée
* @access public
* @static
*/
  public static function labelize($string)
  {
    $string = self::noAccent($string);
    $string = self::toLower($string);
    $string = self::noSpecialCharacters($string, '-');
    $string = preg_replace('#-{2,}#', '-', $string);
    while (preg_match('/.*-$/', $string) > 0) {
      $string = substr($string, 0, -1);
    }
    
    return $string;
  }
  
  
  /**
* Truncates text.
*
* Cuts a string to the length of $length and replaces the last characters
* with the ending if the text is longer than length.
*
* @param string $text String to truncate.
* @param integer $length Length of returned string, including ellipsis.
* @param mixed $ending If string, will be used as Ending and appended to the trimmed string. Can also be an associative array that can contain the last three params of this method.
* @param boolean $exact If false, $text will not be cut mid-word
* @param boolean $consider_html If true, HTML tags would be handled correctly
* @return string Trimmed string.
* @access public
* @static
* @see http://www.ycerdan.fr/php/tronquer-un-texte-en-conservant-les-tags-html-en-php/
*/
  public static function truncate($text, $length = 100, $ending = '...', $exact = true, $consider_html = true)
  {
    if (is_array($ending)) {
      extract($ending);
    }
    if ($consider_html) {
      if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
        return $text;
      }
      $total_length = mb_strlen($ending);
      $open_tags = array();
      $truncate = '';
      preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
      foreach ($tags as $tag) {
        if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
          if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
            array_unshift($open_tags, $tag[2]);
          } else if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
            $pos = array_search($closeTag[1], $open_tags);
            if ($pos !== false) {
              array_splice($open_tags, $pos, 1);
            }
          }
        }
        $truncate .= $tag[1];
  
        $content_length = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
        if ($content_length + $total_length > $length) {
          $left = $length - $total_length;
          $entities_length = 0;
          if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
            foreach ($entities[0] as $entity) {
              if ($entity[1] + 1 - $entities_length <= $left) {
                $left--;
                $entities_length += mb_strlen($entity[0]);
              } else {
                break;
              }
            }
          }
  
          $truncate .= mb_substr($tag[3], 0 , $left + $entities_length);
          break;
        } else {
          $truncate .= $tag[3];
          $total_length += $content_length;
        }
        if ($total_length >= $length) {
          break;
        }
      }
  
    } else {
      if (mb_strlen($text) <= $length) {
        return $text;
      } else {
        $truncate = mb_substr($text, 0, $length - strlen($ending));
      }
    }
    if (!$exact) {
      $spacepos = mb_strrpos($truncate, ' ');
      if (isset($spacepos)) {
        if ($consider_html) {
          $bits = mb_substr($truncate, $spacepos);
          preg_match_all('/<\/([a-z]+)>/', $bits, $dropped_tags, PREG_SET_ORDER);
          if (!empty($dropped_tags)) {
            foreach ($dropped_tags as $closing_tag) {
              if (!in_array($closing_tag[1], $open_tags)) {
                array_unshift($open_tags, $closing_tag[1]);
              }
            }
          }
        }
        $truncate = mb_substr($truncate, 0, $spacepos);
      }
    }
  
    $truncate .= $ending;
  
    if ($consider_html) {
      foreach ($open_tags as $tag) {
        $truncate .= '</'.$tag.'>';
      }
    }
  
    return $truncate;
  }
  
  
  /**
* Modifie une chaine de caractère pour qu'elle ne comporte
* pas de caractères spéciaux pour une expression régulière
*
* @param String $string The string to convert
* @return String The string converting
* @access public
* @static
*/
  public static function transformPattern($string)
  {
    $string = str_replace("?", "\?", $string);
    $string = str_replace("^", "\^", $string);
    $string = str_replace("$", "\$", $string);
    $string = str_replace(".", "\.", $string);
    $string = str_replace("*", "\*", $string);
    $string = str_replace("+", "\+", $string);
    $string = str_replace("[", "\[", $string);
    $string = str_replace("]", "\]", $string);
    $string = str_replace("{", "\{", $string);
    $string = str_replace("}", "\}", $string);
    $string = str_replace("(", "\(", $string);
    $string = str_replace(")", "\)", $string);
    $string = str_replace("#", "\#", $string);
    
    return $string;
  }
  
  
  /**
* Get a string with all accent characters in lwercase
*
* @return String All accent characters in lowercase
* @access public
* @static
*/
  public static function getAccentLower()
  {
    $map = self::getMap();
    
    return implode('', $map['accent_lower']);
  }
  
  
  /**
* Get a string with all accent characters in uppercase
*
* @return String All accent characters in uppercase
* @access public
* @static
*/
  public static function getAccentUpper()
  {
    $map = self::getMap();
    
    return implode('', $map['accent_upper']);
  }
  
  
  /**
* Get a string with all no accent characters in lowercase
*
* @return String All no accent characters in lowercase
* @access public
* @static
*/
  public static function getNoAccentLower()
  {
    $map = self::getMap();
    
    return implode('', $map['no_accent_lower']);
  }
  
  
  /**
* Get a string with all no accent characters in uppercase
*
* @return String All no accent characters in uppercase
* @access public
* @static
*/
  public static function getNoAccentUpper()
  {
    $map = self::getMap();
    
    return implode('', $map['no_accent_upper']);
  }
  
  
  /**
* Clean the content with no CR LF
*
* @param string $content The content to check
* @return string The content clean
* @static
* @access public
*/
  public static function cleanCRLF($content)
  {
    $content = str_replace("\n", '', $content);
    $content = str_replace("\r", '', $content);
    $content = str_replace("\r\n", '', $content);
    
    return $content;
  }
  
  
  /**
* Nettoie une chaine en supprimant les espaces et les tags HTML
* Cette fonction est faite pour être appellée via un array_walk
*
* @param string $value La valeur du tableau
* @param string $key La clé de parcours du tableau
* @access public
* @static
*/
  public static function cleanString(&$value, $key)
  {
    $value = trim(strip_tags($value));
  }
  
  
  /**
* Convert BR tags to nl
*
* @param string The string to convert
* @return string The converted string
* @static
* @access public
*/
  public static function br2nl($string)
  {
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
  }
  
  
  /**
* Convert nl to BR tags
*
* @param string The string to convert
* @return string The converted string
* @static
* @access public
*/
  public static function nl2br($string)
  {
    return strtr($string, array("\r\n" => '<br />', "\r" => '<br />', "\n" => '<br />')); ;
  }
  
  
  /**
* Génére une chaine de caractère aléatoire contenant des lettre (minuscule et majuscule)
* des chiffres et des caractères spéciaux
*
* @param Int $passwordLength Longueur du mot de passe voulu
* @param Bool $numbers Indique si l'on souhaite des chiffres dans le mot de passe
* @param Bool $special Indique si l'on souhaite des caractères spéciaux dans le mot de passe
*/
  public static function genPassword($passwordLength = 6, $numbers = true, $special = true)
  {
    $string = 'abcdefghilklmnopqrstuvwxyz';
    $string .= 'ABCDEFGHILKLMNOPQRSTUVWXYZ';
    if ($numbers) {
      $string .= '0123456789';
    }
    if ($special) {
      $string .= '&é(-è_çà)=$*ù!:;,~#{[|\@]}+£µ%§/.?';
    }
    
    if (!is_numeric($passwordLength) || $passwordLength <= 0 || $passwordLength > 1000) {
      // qui ne respecte rien et essaie de faire planter la fonction, bande de chenapants
      $passwordLength = 6;
    }
    
    $password = '';
    $i = 0;
    $stringLength = strlen($string) - 1;
    do {
      $password .= $string[rand(0, $stringLength)];
      $i++;
    } while($i < $passwordLength);
    
    return $password;
  }
  
  
  /**
* Initialize the map character if neccesary and return it
*
* @return Array The map with characters
* @static
* @access protected
* @todo add language like arab and chinese
*/
  protected static function getMap()
  {
    if(self::$map === null){
      // Array struct : array('no_accent_lower', 'no_accent_upper', 'accent_lower', 'accent_upper', 'html_lower', 'html_upper', 'unicode_lower', 'unicode_upper');
      self::$map = array(
        'no_accent_lower' => array(
          'a', 'a', 'a', 'a', 'a', 'a', 'ae',
          'c',
          'e', 'e', 'e', 'e',
          'i', 'i', 'i', 'i',
          'eth',
          'n',
          'o', 'o', 'o', 'o', 'o', 'o', 'oe',
          'u', 'u', 'u', 'u',
          'y',
          'sz',
          'thorn',
        ),
        'no_accent_upper' => array(
          'A', 'A', 'A', 'A', 'A', 'A', 'AE',
          'C',
          'E', 'E', 'E', 'E',
          'I', 'I', 'I', 'I',
          'ETH',
          'N',
          'O', 'O', 'O', 'O', 'O', 'O', 'OE',
          'U', 'U', 'U', 'U',
          'Y',
          'SZ',
          'THORN',
        ),
        'accent_lower' => array(
          'à', 'á', 'â', 'ã', 'ä', 'å', 'æ',
          'ç',
          'è', 'é', 'ê', 'ë',
          'ì', 'í', 'î', 'ï',
          'ð',
          'ñ',
          'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'œ',
          'ù', 'ú', 'û', 'ü',
          'ý',
          'ß',
          'þ',
        ),
        'accent_upper' => array(
          'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ',
          'Ç',
          'È', 'É', 'Ê', 'Ë',
          'Ì', 'Í', 'Î', 'Ï',
          'Ð',
          'Ñ',
          'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Œ',
          'Ù', 'Ú', 'Û', 'Ü',
          'Ý',
          'ß',
          'Þ',
        ),
        'html_lower' => array(
          'agrave', 'aacute', 'acirc', 'atilde', 'auml', 'aring', 'aelig',
          'ccedil',
          'egrave', 'eacute', 'ecirc', 'euml',
          'igrave', 'iacute', 'icirc', 'iuml',
          'eth',
          'ntilde',
          'ograve', 'oacute', 'ocirc', 'otilde', 'ouml', 'oslash', 'oelig',
          'ugrave', 'uacute', 'ucirc', 'uuml',
          'yacute',
          'szlig',
          'thorn',
        ),
        'html_upper' => array(
          'Agrave', 'Aacute', 'Acirc', 'Atilde', 'Auml', 'Aring', 'AElig',
          'Ccedil',
          'Egrave', 'Eacute', 'Ecirc', 'Euml',
          'Igrave', 'Iacute', 'Icirc', 'Iuml',
          'ETH',
          'Ntilde',
          'Ograve', 'Oacute', 'Ocirc', 'Otilde', 'Ouml', 'Oslash', 'OElig',
          'Ugrave', 'Uacute', 'Ucirc', 'Uuml',
          'Yacute',
          'szlig',
          'THORN',
        ),
        'unicode_lower' => array(
          '224', '225', '226', '227', '228', '229', '230',
          '231',
          '232', '233', '234', '235',
          '236', '237', '238', '239',
          '240',
          '241',
          '242', '243', '244', '245', '246', '248', '339',
          '249', '250', '251', '252',
          '253',
          '223',
          '254',
        ),
        'unicode_upper' => array(
          '192', '193', '194', '195', '196', '197', '198',
          '199',
          '200', '201', '202', '203',
          '204', '205', '206', '207',
          '208',
          '209',
          '210', '211', '212', '213', '214', '216', '338',
          '217', '218', '219', '220',
          '221',
          '223',
          '222',
        ),
      );
    }
    
    return self::$map;
  }
  
  
  /**
* Add the symbol to convert numeric to unicode entities
*
* @param int $unicode the numeric value of unicode
* @access protected
* @static
*/
  protected static function addUnicodeEntities(&$unicode)
  {
    $unicode = '&#'.$unicode.';';
  }
  
  
  /**
* Add the symbol to convert string to html entities
*
* @param string $html the string value of html entities
* @access protected
* @static
*/
  protected static function addHtmlEntities(&$html)
  {
    $html = '&'.$html.';';
  }

  /**
* Add the symbol to convert string to html entities
*
* @param int $nb_indentation le niveau d'indentation
* @param string $separator le séparteur voulu (par défaut : &nbsp;&nbsp; )
* @access public
* @static
* @return string une chaine de caractere pour l'indentation
*/
  public static function addIndentation($nb_indentation, $separator = '&nbsp;&nbsp;')
  {
    
    if (!is_numeric($nb_indentation) || $nb_indentation <= 0 || $nb_indentation > 1000) {
      // qui ne respecte rien et essaie de faire planter la fonction, bande de chenapants
      $nb_indentation = 0;
    }
   
    if(empty($separator) === true || !is_string($separator)){
      $separator = '&nbsp;&nbsp;';
    }
    
    $espace = '';
    for($cpt = 0 ; $cpt < $nb_indentation ; $cpt++){
      $espace .= $separator;
    }
    
    return $espace;

  }
   
}