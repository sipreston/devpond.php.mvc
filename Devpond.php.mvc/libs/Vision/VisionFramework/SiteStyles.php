<?php

namespace Vision\VisionFramework;

class SiteStyles
{
    public $availableStyles = array();

    public function Add(Style $style)
    {
        $this->availableStyles[$style->location . '/' . $style->fileName] = $style;
    }

    public function Remove($key)
    {
        unset($this->availableStyles[$key]);
    }

}