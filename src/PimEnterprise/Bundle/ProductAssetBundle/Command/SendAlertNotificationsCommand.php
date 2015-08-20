<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Command;

use Pim\Bundle\NotificationBundle\Email\MailNotifier;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use PimEnterprise\Bundle\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Generate the missing variation files
 * It can generate all missing variations or missing variations for a specific asset code
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 * TODO: find a better name for the class and the command and a better description for the command
 */
class SendAlertNotificationsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pim:asset:send-notification');
        $this->setDescription(
            'Send a notification and an email if it\'s
             enabled for the user when an Asset will be outdated soon'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $users = $this->getUserRepository()->findAll();

        foreach ($users as $user) {
            if ($user->isEmailNotifications()) {
                $assets = $this->getAssetRepository()->findAllAssetsByEndOfUse($user->getAssetDelayReminder());

                foreach ($assets as &$asset) {
                    $uri = $this->getRouter()->generate('pimee_product_asset_view', ['id' => $asset['id']]);
                    $asset['url'] = $uri;
                }

                $parameters =
                    [
                        'user'   => $user,
                        'assets' => $assets,
                        'nb'     => $user->getAssetDelayReminder(),
                        'unit'   => 'days',
                        'locale' => 'en_US'
                    ];

                $htmlBody = $this->getTemplating()
                    ->render('@PimEnterpriseProductAsset/Email/notification.html.twig', $parameters);
                $txtBody = $this->getTemplating()
                    ->render('@PimEnterpriseProductAsset/Email/notification.txt.twig', $parameters);

                $output->writeln(sprintf('<info>Sending email for user "%s"</info>', $user->getUsername()));
                $this->getMailNotifier()->notify([$user], 'Asset expiration',$txtBody, $htmlBody);
            }
        }

        $output->writeln('<info>Done!</info>');

        return 0;
    }

    /**
     * @return UserRepositoryInterface
     */
    protected function getUserRepository()
    {
        return $this->getContainer()->get('pim_user.repository.user');
    }

    /**
     * @return UserRepositoryInterface
     */
    protected function getAssetRepository()
    {
        return $this->getContainer()->get('pimee_product_asset.repository.asset');
    }

    /**
     * @return RouterInterface
     */
    protected function getRouter()
    {
        return $this->getContainer()->get('router');
    }

    /**
     * @return EngineInterface
     */
    protected function getTemplating()
    {
        return $this->getContainer()->get('templating');
    }

    /**
     * @return MailNotifier
     */
    protected function getMailNotifier()
    {
        return $this->getContainer()->get('pim_notification.email.email_notifier');
    }
}
