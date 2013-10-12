<?php

namespace Gesdon\App;

use Gesdon\Core\Config;
use Gesdon\Database\Don as DBDon;
use Gesdon\Database\DonPeer;
use Gesdon\Database\DonQuery;
use Gesdon\Core\Exception;

class Task extends Main
{
    public function executeGet()
    {
        return $this->render();
    }
}