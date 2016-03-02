<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\PropertyCopierInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Updater\Copier\AttributeCopierInterface;
use Pim\Component\Catalog\Updater\Copier\CopierRegistryInterface;

/**
 * Copy the property of an object to another object property
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPropertyCopier implements PropertyCopierInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var CopierRegistryInterface */
    protected $copierRegistry;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param CopierRegistryInterface               $copierRegistry
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        CopierRegistryInterface $copierRegistry
    ) {
        $this->attributeRepository = $repository;
        $this->copierRegistry      = $copierRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function copyData(
        $fromProduct,
        $toProduct,
        $fromField,
        $toField,
        array $options = []
    ) {
        if (!$fromProduct instanceof ProductInterface || !$toProduct instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Component\Catalog\Model\ProductInterface", "%s" and "%s" provided.',
                    ClassUtils::getClass($fromProduct),
                    ClassUtils::getClass($toProduct)
                )
            );
        }

        $copier = $this->copierRegistry->getCopier($fromField, $toField);
        if (null === $copier) {
            throw new \LogicException(sprintf('No copier found for fields "%s" and "%s"', $fromField, $toField));
        }

        if ($copier instanceof AttributeCopierInterface) {
            $fromAttribute = $this->getAttribute($fromField);
            $toAttribute   = $this->getAttribute($toField);
            $copier->copyAttributeData($fromProduct, $toProduct, $fromAttribute, $toAttribute, $options);
        } else {
            $copier->copyFieldData($fromProduct, $toProduct, $fromField, $toField, $options);
        }

        return $this;
    }

    /**
     * @param string $code
     *
     * @return AttributeInterface|null
     */
    protected function getAttribute($code)
    {
        return $this->attributeRepository->findOneByIdentifier($code);
    }
}
