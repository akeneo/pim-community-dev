<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\InstallerBundle\Command;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\AttributeGroupAccessRepository;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Revoke ALL group on attribute group accesses.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class CleanAttributeGroupAccessesCommand extends Command
{
    protected static $defaultName = 'pimee:installer:clean-attribute-group-accesses';
    protected static $defaultDescription = 'Removing the group "ALL" from attribute groups\' permissions after a clean installation.';

    public function __construct(
        private AttributeGroupAccessRepository $attributeGroupAccessRepository,
        private GroupRepositoryInterface $groupRepository,
        private FeatureFlags $featureFlags
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->featureFlags->isEnabled('permission')) {
            $output->writeln('Removing the group "ALL" from attribute groups\' permissions...');
            $groupAll = $this->groupRepository->getDefaultUserGroup();
            $this->attributeGroupAccessRepository->revokeAccessToGroups([$groupAll]);
            $output->writeln('<info>done !</info>');
        } else {
            $output->writeln('Permission feature is not enabled. Not removing the group "ALL" from attribute groups\' permissions.');
        }

        return Command::SUCCESS;
    }
}
