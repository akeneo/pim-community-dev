<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Remover;

use Akeneo\Bundle\StorageUtilsBundle\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Builder\ProductTemplateBuilderInterface;
use Pim\Bundle\CatalogBundle\Event\AttributeEvents;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductTemplateRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Attribute remover
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRemover implements RemoverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var RemovingOptionsResolverInterface */
    protected $optionsResolver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ProductTemplateBuilderInterface */
    protected $productTplBuilder;

    /** @var ProductTemplateRepositoryInterface */
    protected $productTplRepository;

    /**
     * @param ObjectManager                      $objectManager
     * @param RemovingOptionsResolverInterface   $optionsResolver
     * @param EventDispatcherInterface           $eventDispatcher
     * @param ProductTemplateBuilderInterface    $productTplBuilder
     * @param ProductTemplateRepositoryInterface $productTplRepository
     */
    public function __construct(
        ObjectManager $objectManager,
        RemovingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher,
        ProductTemplateBuilderInterface $productTplBuilder = null,
        ProductTemplateRepositoryInterface $productTplRepository = null
    ) {
        $this->objectManager = $objectManager;
        $this->optionsResolver = $optionsResolver;
        $this->eventDispatcher = $eventDispatcher;
        $this->productTplBuilder = $productTplBuilder;
        $this->productTplRepository = $productTplRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($attribute, array $options = [])
    {
        if (!$attribute instanceof AttributeInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects an "%s", "%s" provided.',
                    'Pim\Bundle\CatalogBundle\Model\AttributeInterface',
                    ClassUtils::getClass($attribute)
                )
            );
        }

        $options = $this->optionsResolver->resolveRemoveOptions($options);

        $attributeId = $attribute->getId();
        $this->eventDispatcher->dispatch(AttributeEvents::PRE_REMOVE, new RemoveEvent($attribute, $attributeId));

        $this->removeFromProductTemplate($attribute);
        $this->objectManager->remove($attribute);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        $this->eventDispatcher->dispatch(AttributeEvents::POST_REMOVE, new RemoveEvent($attribute, $attributeId));
    }

    /**
     * Remove an attribute from product template
     *
     * @param AttributeInterface $attribute
     */
    protected function removeFromProductTemplate(AttributeInterface $attribute)
    {
        if (null === $this->productTplBuilder || null === $this->productTplRepository) {
            return;
        }

        foreach ($this->productTplRepository->findAll() as $productTemplate) {
            if ($productTemplate->hasValueForAttribute($attribute)) {
                $this->productTplBuilder->removeAttribute($productTemplate, $attribute);

                $attributeCodes = $productTemplate->getAttributeCodes();
                empty($attributeCodes)
                    ? $this->objectManager->remove($productTemplate)
                    : $this->objectManager->persist($productTemplate);
            }
        }
    }
}
