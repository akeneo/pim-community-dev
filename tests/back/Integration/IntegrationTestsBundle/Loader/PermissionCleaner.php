<?php

namespace AkeneoEnterprise\Test\IntegrationTestsBundle\Loader;

use Akeneo\Platform\Bundle\InstallerBundle\Command\CleanAttributeGroupAccessesCommand;
use Akeneo\Platform\Bundle\InstallerBundle\Command\CleanCategoryAccessesCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

class PermissionCleaner
{
    /** @var KernelInterface */
    protected $kernel;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Remove the group All after an import
     *
     * @throws \Exception
     */
    public function cleanPermission()
    {
        $application = new Application();

        $cleanCategoryRight = $application->add($this->kernel->getContainer()->get(CleanCategoryAccessesCommand::class));
        $cleanAttributeGroupRight = $application->add($this->kernel->getContainer()->get(CleanAttributeGroupAccessesCommand::class));

        $cleanCategoryRightCommand = new CommandTester($cleanCategoryRight);
        $cleanAttributeGroupRightCommand = new CommandTester($cleanAttributeGroupRight);

        $cleanCategoryRightCode = $cleanCategoryRightCommand->execute([]);
        $cleanAttributeGroupRightCode = $cleanAttributeGroupRightCommand->execute([]);

        if (0 !== $cleanCategoryRightCode && 0 !== $cleanAttributeGroupRightCode) {
            throw new \Exception('Failed to clean accesses.');
        }
    }
}
