<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Registry of adders
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AdderRegistry implements AdderRegistryInterface
{
    /** @var AttributeAdderInterface[] priorized attribute adders */
    protected $attributeAdders = [];

    /** @var FieldAdderInterface[] priorized field adders */
    protected $fieldAdders = [];

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $repository)
    {
        $this->attributeRepository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function register(AdderInterface $adder)
    {
        if ($adder instanceof FieldAdderInterface) {
            $this->fieldAdders[] = $adder;
        }
        if ($adder instanceof AttributeAdderInterface) {
            $this->attributeAdders[] = $adder;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdder($property)
    {
        $attribute = $this->getAttribute($property);
        if (null !== $attribute) {
            $adder = $this->getAttributeAdder($attribute);
        } else {
            $adder = $this->getFieldAdder($property);
        }

        return $adder;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldAdder($field)
    {
        foreach ($this->fieldAdders as $adder) {
            if ($adder->supportsField($field)) {
                return $adder;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeAdder(AttributeInterface $attribute)
    {
        foreach ($this->attributeAdders as $adder) {
            if ($adder->supportsAttribute($attribute)) {
                return $adder;
            }
        }

        return null;
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
