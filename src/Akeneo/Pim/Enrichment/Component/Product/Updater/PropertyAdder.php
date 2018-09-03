<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AttributeAdderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyAdderInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Adds a data in the property of an object
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PropertyAdder implements PropertyAdderInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var AdderRegistryInterface */
    protected $adderRegistry;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param AdderRegistryInterface                $adderRegistry
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        AdderRegistryInterface $adderRegistry
    ) {
        $this->attributeRepository = $repository;
        $this->adderRegistry = $adderRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function addData($product, $field, $data, array $options = [])
    {
        if (!$product instanceof EntityWithValuesInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($product),
                EntityWithValuesInterface::class
            );
        }

        $adder = $this->adderRegistry->getAdder($field);
        if (null === $adder) {
            throw new \LogicException(sprintf('No adder found for field "%s"', $field));
        }

        if ($adder instanceof AttributeAdderInterface) {
            $attribute = $this->getAttribute($field);
            $adder->addAttributeData($product, $attribute, $data, $options);
        } else {
            $adder->addFieldData($product, $field, $data, $options);
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
