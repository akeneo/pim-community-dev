<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\InstallerBundle\Command;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Revoke ALL group on category accesses.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class CleanCategoryAccessesCommand extends Command
{
    protected static $defaultName = 'pimee:installer:clean-category-accesses';
    protected static $defaultDescription = 'Removing the group "ALL" from categories\' permissions after a clean installation.';

    public function __construct(
        private CategoryAccessRepository $accessRepository,
        private GroupRepositoryInterface $groupRepository,
        private ObjectManager $objectManager,
        private FeatureFlags $featureFlags
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}s
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->featureFlags->isEnabled('permission')) {
            $output->writeln('Removing the group "ALL" from categories\' permissions...');
            $groupAll = $this->groupRepository->getDefaultUserGroup();
            $this->accessRepository->revokeAccessToGroups([$groupAll]);
            $this->objectManager->flush();
            $output->writeln('<info>done !</info>');
        } else {
            $output->writeln('Permission feature is not enabled. Not removing the group "ALL" from categories\' permissions.');
        }

        return Command::SUCCESS;
    }
}
