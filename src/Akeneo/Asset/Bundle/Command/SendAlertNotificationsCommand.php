<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Command;

use Akeneo\Asset\Component\Repository\AssetRepositoryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifier;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Send email to the users based on their configuration when an asset is near to expiration
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class SendAlertNotificationsCommand extends Command
{
    protected static $defaultName = 'pim:asset:send-expiration-notification';

    /** @var string */
    protected $htmlBodyTemplate = '@AkeneoAsset/Email/notification.html.twig';

    /** @var string */
    protected $textBodyTemplate = '@AkeneoAsset/Email/notification.txt.twig';

    /** @var string */
    protected $baseUrl = null;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var RouterInterface */
    private $router;

    /** @var EngineInterface */
    private $templateEngine;

    /** @var MailNotifier */
    private $notifier;

    public function __construct(
        UserRepositoryInterface $userRepository,
        AssetRepositoryInterface $assetRepository,
        RouterInterface $router,
        EngineInterface $templateEngine,
        MailNotifier $notifier
    ) {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->assetRepository = $assetRepository;
        $this->router = $router;
        $this->templateEngine = $templateEngine;
        $this->notifier = $notifier;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription(
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

        $users = $this->userRepository->findBy(['emailNotifications' => true]);

        foreach ($users as $user) {
            $assets = $this->assetRepository
                ->findExpiringAssets(new \DateTime(), $user->getProperty('asset_delay_reminder'));

            foreach ($assets as &$asset) {
                $uri = $this->router->generate('pimee_product_asset_edit', ['id' => $asset['id']]);
                $asset['url'] = $uri;
            }

            if (!empty($assets)) {
                $parameters = [
                    'user'    => $user,
                    'assets'  => $assets,
                    'baseUrl' => $baseUrl,
                    'nb'      => $user->getProperty('asset_delay_reminder'),
                    'unit'    => 'days',
                    'locale'  => 'en_US'
                ];

                $htmlBody = $this->templateEngine
                    ->render($this->htmlBodyTemplate, $parameters);
                $txtBody = $this->templateEngine
                    ->render($this->textBodyTemplate, $parameters);

                $this->notifier->notify([$user], 'Asset expiration', $txtBody, $htmlBody);
            }
        }

        $output->writeln('<info>Done!</info>');

        return 0;
    }
}
