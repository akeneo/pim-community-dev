<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\PropertyRemoverInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Updater\Remover\AttributeRemoverInterface;
use Pim\Component\Catalog\Updater\Remover\RemoverRegistryInterface;

/**
 * Removes a data in the property of an object
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PropertyRemover implements PropertyRemoverInterface
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
        $this->removerRegistry = $removerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function removeData($entityWithValues, $field, $data, array $options = [])
    {
        if (!$entityWithValues instanceof EntityWithValuesInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($entityWithValues),
                EntityWithValuesInterface::class
            );
        }

        $remover = $this->removerRegistry->getRemover($field);
        if (null === $remover) {
            throw new \LogicException(sprintf('No remover found for field "%s"', $field));
        }

        if ($remover instanceof AttributeRemoverInterface) {
            $attribute = $this->getAttribute($field);
            $remover->removeAttributeData($entityWithValues, $attribute, $data, $options);
        } else {
            $remover->removeFieldData($entityWithValues, $field, $data, $options);
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
