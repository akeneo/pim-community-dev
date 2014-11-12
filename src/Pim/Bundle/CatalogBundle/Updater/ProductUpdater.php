<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Updater\Copier\CopierRegistryInterface;
use Pim\Bundle\CatalogBundle\Updater\Setter\SetterRegistryInterface;

/**
 * Update many products at a time
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductUpdater implements ProductUpdaterInterface
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var SetterRegistryInterface */
    protected $setterRegistry;

    /** @var CopierRegistryInterface */
    protected $copierRegistry;

    /**
     * @param AttributeRepository     $repository
     * @param SetterRegistryInterface $setterRegistry
     * @param CopierRegistryInterface $copierRegistry
     */
    public function __construct(
        AttributeRepository $repository,
        SetterRegistryInterface $setterRegistry,
        CopierRegistryInterface $copierRegistry
    ) {
        $this->attributeRepository = $repository;
        $this->setterRegistry = $setterRegistry;
        $this->copierRegistry = $copierRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(array $products, $field, $data, $locale = null, $scope = null)
    {
        $attribute = $this->getAttribute($field);
        $setter = $this->setterRegistry->get($attribute);
        $setter->setValue($products, $attribute, $data, $locale, $scope);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function copyValue(
        array $products,
        $fromField,
        $toField,
        $fromLocale = null,
        $toLocale = null,
        $fromScope = null,
        $toScope = null
    ) {
        $fromAttribute = $this->getAttribute($fromField);
        $toAttribute = $this->getAttribute($toField);
        $copier = $this->copierRegistry->get($fromAttribute, $toAttribute);
        $copier->copyValue($products, $fromAttribute, $toAttribute, $fromLocale, $toLocale, $fromScope, $toScope);

        return $this;
    }

    /**
     * Fetch the attribute by its code
     *
     * @param string $code
     *
     * @throws \LogicException
     *
     * @return AttributeInterface
     */
    protected function getAttribute($code)
    {
        $attribute = $this->attributeRepository->findOneBy(['code' => $code]);
        if ($attribute === null) {
            throw new \LogicException(sprintf('Unknown attribute "%s".', $code));
        }

        return $attribute;
    }
}
