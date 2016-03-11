<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Akeneo\Component\Console\CommandLauncher;
use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\ProductEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Remove associated product on product remove
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveOutdatedProductsFromAssociationsSubscriber implements EventSubscriberInterface
{
    /** @var CommandLauncher */
    protected $launcher;

    /** @var string */
    protected $logFile;

    /**
     * @param CommandLauncher $launcher
     * @param string          $logFile
     */
    public function __construct(CommandLauncher $launcher, $logFile)
    {
        $this->launcher = $launcher;
        $this->logFile = $logFile;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_REMOVE      => 'removeAssociatedProduct',
            ProductEvents::POST_MASS_REMOVE => 'removeAssociatedProducts'
        ];
    }

    /**
     * Removes product associations for a single deleted product.
     *
     * Both the deleted product entity and its ID are provided through
     * the event.
     *
     * @param RemoveEvent $event
     *
     * @return null
     */
    public function removeAssociatedProduct(RemoveEvent $event)
    {
        $subject = $event->getSubject();

        if (!$subject instanceof ProductInterface) {
            return;
        }

        $command = sprintf(
            'pim:product:remove-from-associations %s',
            $event->getSubjectId()
        );

        $this->launcher->executeBackground($command, $this->logFile);
    }

    /**
     * Removes product associations for a list of deleted products.
     *
     * Only the list of the deleted IDs is provided through the event.
     *
     * @param GenericEvent $event
     */
    public function removeAssociatedProducts(GenericEvent $event)
    {
        $productIds = implode(',', $event->getSubject());

        $command = sprintf(
            'pim:product:remove-from-associations %s',
            $productIds
        );

        $this->launcher->executeBackground($command, $this->logFile);
    }
}
