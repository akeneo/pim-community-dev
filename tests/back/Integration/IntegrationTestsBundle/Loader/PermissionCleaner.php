<?php

namespace AkeneoEnterprise\Test\IntegrationTestsBundle\Loader;

use Akeneo\Platform\Bundle\InstallerBundle\Command\CleanAttributeGroupAccessesCommand;
use Akeneo\Platform\Bundle\InstallerBundle\Command\CleanCategoryAccessesCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

class PermissionCleaner
{
    /** @var CleanCategoryAccessesCommand */
    private $cleanCategoryAccessesCommand;

    /** @var CleanAttributeGroupAccessesCommand */
    private $cleanAttributeGroupAccessesCommand;

    public function __construct(
        CleanCategoryAccessesCommand $cleanCategoryAccessesCommand,
        CleanAttributeGroupAccessesCommand $cleanAttributeGroupAccessesCommand
    ) {
        $this->cleanCategoryAccessesCommand = $cleanCategoryAccessesCommand;
        $this->cleanAttributeGroupAccessesCommand = $cleanAttributeGroupAccessesCommand;
    }

    /**
     * Remove the group All after an import
     *
     * @throws \Exception
     */
    public function cleanPermission()
    {
        $application = new Application();

        $cleanCategoryRight = $application->add($this->cleanCategoryAccessesCommand);
        $cleanAttributeGroupRight = $application->add($this->cleanAttributeGroupAccessesCommand);

        $cleanCategoryRightCommand = new CommandTester($cleanCategoryRight);
        $cleanAttributeGroupRightCommand = new CommandTester($cleanAttributeGroupRight);

        $cleanCategoryRightCode = $cleanCategoryRightCommand->execute([]);
        $cleanAttributeGroupRightCode = $cleanAttributeGroupRightCommand->execute([]);

        if (0 !== $cleanCategoryRightCode && 0 !== $cleanAttributeGroupRightCode) {
            throw new \Exception('Failed to clean accesses.');
        }
    }
}
