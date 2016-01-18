<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Builder\ProductTemplateBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductTemplateRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Cascade the removal of the attributes in the product templates.
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @todo      We should configure cascading properly in doctrine to avoid such event subscriber
 */
class ProductTemplateAttributeSubscriber implements EventSubscriberInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var ProductTemplateBuilderInterface */
    protected $productTplBuilder;

    /** @var ProductTemplateRepositoryInterface */
    protected $productTplRepository;

    /**
     * @param ObjectManager                      $objectManager
     * @param ProductTemplateBuilderInterface    $productTplBuilder
     * @param ProductTemplateRepositoryInterface $productTplRepository
     */
    public function __construct(
        ObjectManager $objectManager,
        ProductTemplateBuilderInterface $productTplBuilder,
        ProductTemplateRepositoryInterface $productTplRepository
    ) {
        $this->objectManager        = $objectManager;
        $this->productTplBuilder    = $productTplBuilder;
        $this->productTplRepository = $productTplRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [StorageEvents::PRE_REMOVE => 'preRemove'];
    }

    /**
     * @param RemoveEvent $event
     */
    public function preRemove(RemoveEvent $event)
    {
        $subject = $event->getSubject();

        if (!$subject instanceof AttributeInterface) {
            return;
        }

        $productTemplates = $this->productTplRepository->findByAttribute($subject);

        foreach ($productTemplates as $productTemplate) {
            $this->productTplBuilder->removeAttribute($productTemplate, $subject);
            $attributeCodes = $productTemplate->getAttributeCodes();

            if (empty($attributeCodes)) {
                $this->objectManager->remove($productTemplate);
            } else {
                $this->objectManager->persist($productTemplate);
            }
        }
    }
}
