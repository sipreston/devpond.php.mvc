<?php
namespace Vision\VisionDatabase\Interfaces;

use Model\User;
use Vision\VisionDatabase\Providers\DbProviderParameters;

interface IInstaller
{
    public function __construct(DbProviderParameters $params = null, $type = null, $initOnly = false);

    public function setAdminUser(User $adminUser);

    public function setRootDbParams(DbProviderParameters $params);

    public function setApplicationDbParams(DbProviderParameters $params);

    public function install();

    public function uninstall();

    public function setDbType($dbType = null);

    public function getView($type);
}