<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Remover;

use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Repository\ProductTemplateRepositoryInterface;
use Pim\Bundle\CatalogBundle\Event\AttributeEvents;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductTemplateBuilderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

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
    protected $productTemplateBuilder;

    /** @var ProductTemplateRepositoryInterface */
    protected $productTemplateRepository;

    /**
     * @param ObjectManager                      $objectManager
     * @param RemovingOptionsResolverInterface   $optionsResolver
     * @param EventDispatcherInterface           $eventDispatcher
     * @param ProductTemplateBuilderInterface    $productTemplateBuilder
     * @param ProductTemplateRepositoryInterface $productTemplateRepository
     */
    public function __construct(
        ObjectManager $objectManager,
        RemovingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher,
        ProductTemplateBuilderInterface $productTemplateBuilder = null,
        ProductTemplateRepositoryInterface $productTemplateRepository = null
    ) {
        $this->objectManager = $objectManager;
        $this->optionsResolver = $optionsResolver;
        $this->eventDispatcher = $eventDispatcher;
        $this->productTemplateBuilder = $productTemplateBuilder;
        $this->productTemplateRepository = $productTemplateRepository;
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
        $this->eventDispatcher->dispatch(AttributeEvents::PRE_REMOVE, new GenericEvent($attribute));

        $this->removeFromProductTemplate($attribute);
        $this->objectManager->remove($attribute);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        $this->eventDispatcher->dispatch(AttributeEvents::POST_REMOVE, new GenericEvent($attribute));
    }

    /**
     * Remove an attribute from product template
     *
     * @param AttributeInterface $attribute
     */
    protected function removeFromProductTemplate(AttributeInterface $attribute)
    {
        if (null === $this->productTemplateBuilder || null === $this->productTemplateRepository) {
            return;
        }

        foreach ($this->productTemplateRepository->findAll() as $productTemplate) {
            if ($productTemplate->hasValueForAttribute($attribute)) {
                $this->productTemplateBuilder->removeAttribute($productTemplate, $attribute);

                $attributeCodes = $productTemplate->getAttributeCodes();
                empty($attributeCodes)
                    ? $this->objectManager->remove($productTemplate)
                    : $this->objectManager->persist($productTemplate);
            }
        }
    }
}
