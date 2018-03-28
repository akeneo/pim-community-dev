<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\PropertyCopierInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Updater\Copier\AttributeCopierInterface;
use Pim\Component\Catalog\Updater\Copier\CopierRegistryInterface;

/**
 * Copy the property of an object to another object property
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PropertyCopier implements PropertyCopierInterface
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
        $this->copierRegistry = $copierRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function copyData(
        $fromEntityWithValues,
        $toEntityWithValues,
        $fromField,
        $toField,
        array $options = []
    ) {
        if (!$fromEntityWithValues instanceof EntityWithValuesInterface ||
            !$toEntityWithValues instanceof EntityWithValuesInterface
        ) {
            throw new InvalidObjectException(
                ClassUtils::getClass($fromEntityWithValues),
                EntityWithValuesInterface::class,
                sprintf(
                    'Expects a "%s", "%s" and "%s" provided.',
                    EntityWithValuesInterface::class,
                    ClassUtils::getClass($fromEntityWithValues),
                    ClassUtils::getClass($toEntityWithValues)
                )
            );
        }

        $copier = $this->copierRegistry->getCopier($fromField, $toField);
        if (null === $copier) {
            throw new \LogicException(sprintf('No copier found for fields "%s" and "%s"', $fromField, $toField));
        }

        if ($copier instanceof AttributeCopierInterface) {
            $fromAttribute = $this->getAttribute($fromField);
            $toAttribute = $this->getAttribute($toField);
            $copier->copyAttributeData(
                $fromEntityWithValues,
                $toEntityWithValues,
                $fromAttribute,
                $toAttribute,
                $options
            );
        } else {
            $copier->copyFieldData($fromEntityWithValues, $toEntityWithValues, $fromField, $toField, $options);
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
