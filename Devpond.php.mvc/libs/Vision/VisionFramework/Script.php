<?php

namespace Vision\VisionFramework;

class Script
{
    public $fileName;
    public $location;
    public $type = null;
    public $isExternal;
    public $afterPageLoad;
    public $loadFirst;
    public $loadOrder = 0;

    public function __construct($fileName, $location, $loadFirst = false, $type = null, $isExternal = false,
                                $afterPageLoad = false)
    {
        $this->fileName = $fileName;
        $this->location = $location;
        $this->type = $type;
        $this->isExternal = $isExternal;
        $this->afterPageLoad = $afterPageLoad;
        $this->loadFirst = $loadFirst;
    }
}