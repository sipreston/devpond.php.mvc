<?php

namespace Vision\VisionFramework;

abstract class ViewResolver
{
    public function __construct()
    {

    }

    abstract function getViewData();
}