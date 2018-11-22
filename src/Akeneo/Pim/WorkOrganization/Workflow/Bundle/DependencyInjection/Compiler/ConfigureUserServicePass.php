<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Anael Chardan <anael.chardan@akeneo.com>
 */
class ConfigureUserServicePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $userNormalizer = $container->getDefinition('pim_user.normalizer.user');
        $userNormalizer->addArgument('proposals_to_review_notification');
        $userNormalizer->addArgument('proposals_state_notifications');

        $userWorkflowNormalizer = $container->getDefinition('pimee_workflow.normalizer.user');
        $userExtraNormalizers = $userNormalizer->getArgument(5);

        $userExtraNormalizers[] = $userWorkflowNormalizer;

        $userNormalizer->replaceArgument(5, $userExtraNormalizers);

        $userUpdater = $container->getDefinition('pim_user.updater.user');
        $userUpdater->addArgument('proposals_to_review_notification');
        $userUpdater->addArgument('proposals_state_notifications');

        $userFactory = $container->getDefinition('pim_user.factory.user');

        $defaultProposalsToReviewNotification = $container->getDefinition('pimee_workflow.factory.user.default_proposals_to_review_notification');
        $defaultProposalsStateNotifications = $container->getDefinition('pimee_workflow.factory.user.default_proposals_state_notifications');

        $userFactory->addArgument($defaultProposalsToReviewNotification);
        $userFactory->addArgument($defaultProposalsStateNotifications);
    }
}
