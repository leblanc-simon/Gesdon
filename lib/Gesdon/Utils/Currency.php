<?php

namespace Gesdon\Utils;

class Currency
{
  static public function convertCurrency($amount)
  {
    return str_replace(array(',', ' ', ' '), array('.', '', ''), $amount);
  }
}