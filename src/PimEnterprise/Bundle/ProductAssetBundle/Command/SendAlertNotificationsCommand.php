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

use Pim\Bundle\NotificationBundle\Email\NotifierInterface;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Send email to the users based on their configuration when an asset is near to expiration
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class SendAlertNotificationsCommand extends ContainerAwareCommand
{
    /** @var string */
    protected $htmlBodyTemplate = '@PimEnterpriseProductAsset/Email/notification.html.twig';

    /** @var string */
    protected $textBodyTemplate = '@PimEnterpriseProductAsset/Email/notification.txt.twig';

    /** @var string */
    protected $baseUrl = null;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pim:asset:send-expiration-notification')
            ->setDescription(
                'Send a notification and an email if it\'s
                 enabled for the user when an Asset will be outdated soon'
            )
            ->addArgument(
                'base-url',
                InputArgument::OPTIONAL,
                'The base url for the website'
            )
            ->addArgument(
                'html-template',
                InputArgument::OPTIONAL,
                'The html template you want to use for emails'
            )
            ->addArgument(
                'text-template',
                InputArgument::OPTIONAL,
                'The text template you want to use for emails'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null !== $htmlBodyTemplate = $input->getArgument('html-template')) {
            $this->htmlBodyTemplate = $htmlBodyTemplate;
        }

        if (null !== $textBodyTemplate = $input->getArgument('text-template')) {
            $this->textBodyTemplate = $textBodyTemplate;
        }

        if (null !== $baseUrl = $input->getArgument('base-url')) {
            $this->baseUrl = $baseUrl;
        }

        $users = $this->getUserRepository()->findBy(['emailNotifications' => true]);

        foreach ($users as $user) {
            $assets = $this->getAssetRepository()
                ->findExpiringAssets(new \DateTime(), $user->getAssetDelayReminder());

            foreach ($assets as &$asset) {
                $uri = $this->getRouter()->generate('pimee_product_asset_edit', ['id' => $asset['id']]);
                $asset['url'] = $uri;
            }

            if (!empty($assets)) {
                $parameters = [
                    'user'    => $user,
                    'assets'  => $assets,
                    'baseUrl' => $baseUrl,
                    'nb'      => $user->getAssetDelayReminder(),
                    'unit'    => 'days',
                    'locale'  => 'en_US'
                ];

                $htmlBody = $this->getTemplating()
                    ->render($this->htmlBodyTemplate, $parameters);
                $txtBody = $this->getTemplating()
                    ->render($this->textBodyTemplate, $parameters);

                $this->getMailNotifier()->notify([$user], 'Asset expiration', $txtBody, $htmlBody);
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
     * @return NotifierInterface
     */
    protected function getMailNotifier()
    {
        return $this->getContainer()->get('pim_notification.email.email_notifier');
    }
}
