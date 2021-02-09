<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Subscribers;

use Akeneo\AssetManager\Domain\Event\AssetDeletedEvent;
use Akeneo\AssetManager\Domain\Event\AssetsDeletedEvent;
use Akeneo\AssetManager\Domain\Event\AttributeOptionsDeletedEvent;
use Akeneo\Tool\Component\Console\CommandLauncher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RefreshAssetsSubscriber implements EventSubscriberInterface
{
    /** @var CommandLauncher  */
    private $commandLauncher;

    public function __construct(CommandLauncher $commandLauncher)
    {
        $this->commandLauncher = $commandLauncher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
//        To activate when we improve the refresh performances
//        return [
//            AssetDeletedEvent::class => 'onEvent',
//            AssetsDeletedEvent::class => 'onEvent',
//            AttributeOptionsDeletedEvent::class => 'onEvent',
//        ];
        return [];
    }

    public function onEvent(): void
    {
    }
}
