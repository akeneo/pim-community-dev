<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\PropertyRemoverInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Component\Catalog\Updater\Remover\RemoverRegistryInterface;

/**
 * Removes a data in the property of an object
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPropertyRemover implements PropertyRemoverInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var RemoverRegistryInterface */
    protected $removerRegistry;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param RemoverRegistryInterface              $removerRegistry
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        RemoverRegistryInterface $removerRegistry
    ) {
        $this->attributeRepository = $repository;
        $this->removerRegistry     = $removerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function removeData($product, $field, $data, array $options = [])
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\CatalogBundle\Model\ProductInterface", "%s" provided.',
                    ClassUtils::getClass($product)
                )
            );
        }

        $attribute = $this->getAttribute($field);
        if (null !== $attribute) {
            $remover = $this->removerRegistry->getAttributeRemover($attribute);
        } else {
            $remover = $this->removerRegistry->getFieldRemover($field);
        }

        if (null === $remover) {
            throw new \LogicException(sprintf('No remover found for field "%s"', $field));
        }

        if (null !== $attribute) {
            $remover->removeAttributeData($product, $attribute, $data, $options);
        } else {
            $remover->removeFieldData($product, $field, $data, $options);
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
        $attribute = $this->attributeRepository->findOneByIdentifier($code);

        return $attribute;
    }
}
