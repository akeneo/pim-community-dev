<?php

namespace Acme\Bundle\AppBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventStoreSubscriber implements EventSubscriberInterface
{
    private $productNormalizer;
    private $versionRepository;
    private $logger;

    public function __construct(
        NormalizerInterface $productNormalizer,
        VersionRepositoryInterface $versionRepository,
        LoggerInterface $logger
    )
    {
        $this->productNormalizer = $productNormalizer;
        $this->versionRepository = $versionRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE     => 'savedProduct',
            StorageEvents::POST_SAVE_ALL => 'savedProducts',
        ];
    }

    public function savedProduct(GenericEvent $event)
    {
        // here is a possibility with current constraints
        $object = $event->getSubject();
        // checking following option allow to process only products which have been unitary flushed in database, if the
        // product has been saved through a bulk save, it will be handled by savedProducts().
        //
        // imho, this is not a very elegant solution because we expose a very internal option, the "flush" and we would
        // get rid of this saver option at some point.
        $isFlushed = $event->getArgument('flush');
        if ($object instanceof ProductInterface && $isFlushed) {
            $this->buildEvent($object);
        }
    }

    public function savedProducts(GenericEvent $event)
    {
        $collection = $event->getSubject();
        foreach ($collection as $object) {
            if ($object instanceof ProductInterface) {
                $this->buildEvent($object);
            }
        }
    }

    private function buildEvent(ProductInterface $product)
    {
        $snapshot = $this->productNormalizer->normalize($product, 'json');
        // please notice that versioning system is pretty legacy, it
        // - has been quickly designed/developed to display changes only,
        // - uses a flat format in the snapshot field,
        // - is plugged on doctrine post flush / flush
        //
        // meaning that the changeset may be not enough reliable for what you're trying to achieve
        //
        // FYI, we have a story in our technical roadmap to revamp it to,
        // - plug it on Saver events and not on doctrine (we're trying to properly decouple our business code from doctrine)
        // - use the json/structured format (which is our standard internal format)
        // - use ObjectUpdater API to implement the EE revert feature (this API uses the structured format as changes to apply)
        $lastVersion = $this->versionRepository->getNewestLogEntry(ClassUtils::getClass($product), $product->getId());
        $snapshot['changeset'] = $lastVersion->getChangeset();
        $this->logger->info(
            sprintf('Product %s has been saved, event content sample %s', $product->getIdentifier(), print_r($snapshot, true))
        );
    }
}
