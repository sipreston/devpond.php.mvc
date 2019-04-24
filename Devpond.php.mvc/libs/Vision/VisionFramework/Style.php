<?php

namespace Vision\VisionFramework;

class Style
{
    public $fileName;
    public $location;
    public $type = null;
    public $isExternal;

    public function __construct($fileName, $location, $type = 'stylesheet', $isExternal = false)
    {
        $this->fileName = $fileName;
        $this->location = $location;
        $this->type = $type;
        $this->isExternal = $isExternal;
    }
}