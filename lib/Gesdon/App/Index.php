<?php

namespace Gesdon\App;

class Index extends Main
{
    public function executeGet()
    {
        return $this->render();
    }
}