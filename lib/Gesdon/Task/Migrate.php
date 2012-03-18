<?php

namespace Gesdon\Task;

use Gesdon\Core\Exception;
use Gesdon\Database\Don;
use Gesdon\Database\DonPeer;
use Gesdon\Database\DonQuery;
use Gesdon\Database\Donateur;
use Gesdon\Database\DonateurPeer;
use Gesdon\Database\DonateurQuery;
use Gesdon\Database\CmcicInfo;
use Gesdon\Database\CmcicInfoPeer;
use Gesdon\Database\CmcicInfoQuery;
use Gesdon\Database\PaypalInfo;
use Gesdon\Database\PaypalInfoPeer;
use Gesdon\Database\PaypalInfoQuery;

class Migrate extends BaseTask
{
  public function __construct($args = array())
  {
    parent::__construct($args);
  }
  
  
  public function run()
  {
  }
}