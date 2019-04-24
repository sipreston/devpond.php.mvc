<?php

namespace Vision\VisionDatabase\Installers;

/**
 * Class DbInstallerType
 * @package Vision\VisionDatabase\Installers
 *
 * @property $ProviderName
 * @property $DbType
 * @property $InstallerClass
 * @property $Description
 */
class DbInstallerType
{
    /**
     * @var string
     */
    public $ProviderName;
    public $DbType = 'STANDARD';
    public $InstallerClass;
    public $Description = '';
}