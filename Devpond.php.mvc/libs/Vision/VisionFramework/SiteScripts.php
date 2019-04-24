<?php

namespace Vision\VisionFramework;

class SiteScripts
{
    public $availableScripts = array();
    public $onPageLoadScripts = array();
    public $afterPageLoadScripts = array();

    public function Add(Script $script)
    {
        if($script->afterPageLoad == true)
        {
            $this->afterPageLoadScripts[] = $script;
        }
        else
        {
            $this->onPageLoadScripts[] = $script;
        }
        $this->availableScripts[$script->location . '/' .$script->fileName] = $script;
    }

    public function Remove($key)
    {
        unset($this->availableScripts[$key]);
        unset($this->afterPageLoadScripts[$key]);
        unset($this->onPageLoadScripts[$key]);
    }

    public function Sort($sortMethod = 'LoadFirst')
    {
        array_multisort($this->onPageLoadScripts, SORT_DESC);
    }

}