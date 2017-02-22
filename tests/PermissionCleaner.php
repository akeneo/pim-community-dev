<?php

namespace Akeneo\TestEnterprise\Integration;

use PimEnterprise\Bundle\InstallerBundle\Command\CleanAttributeGroupAccessesCommand;
use PimEnterprise\Bundle\InstallerBundle\Command\CleanCategoryAccessesCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

class PermissionCleaner
{
    /**
     * Remove the group All after an import
     *
     * @param KernelInterface $kernel
     *
     * @throws \Exception
     */
    public function cleanPermission(KernelInterface $kernel)
    {
        $application = new Application();
        $cleanCategoryRight = $application->add(new CleanCategoryAccessesCommand());
        $cleanAttributeGroupRight = $application->add(new CleanAttributeGroupAccessesCommand());
        $cleanCategoryRight->setContainer($kernel->getContainer());
        $cleanAttributeGroupRight->setContainer($kernel->getContainer());
        $cleanCategoryRightCommand = new CommandTester($cleanCategoryRight);
        $cleanAttributeGroupRightCommand = new CommandTester($cleanAttributeGroupRight);

        $cleanCategoryRightCode = $cleanCategoryRightCommand->execute([]);
        $cleanAttributeGroupRightCode = $cleanAttributeGroupRightCommand->execute([]);

        if (0 !== $cleanCategoryRightCode && 0 !== $cleanAttributeGroupRightCode) {
            throw new \Exception('Failed to clean accesses.');
        }
    }
}
