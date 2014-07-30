<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Attribute option flat denormalizer used for following attribute types:
 * - pim_catalog_simpleselect
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeOptionDenormalizer extends AbstractValueDenormalizer
{
    /** @var AttributeOptionRepository */
    protected $repository;

    /**
     * @param AttributeOptionRepository $repository
     */
    public function __construct(array $supportedTypes, AttributeOptionRepository $repository)
    {
        parent::__construct($supportedTypes);

        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $value = $context['value'];
        $option = $this->findEntity(
            $this->prepareOptionCode($data, $value)
        );

        return $option;
    }

    /**
     * Prepare option code for AttributeOptionRepository::findByReference
     *
     * @deprecated AttributeOptionRepository::findByReference should take a code as parameter
     *
     * @param string                $data
     * @param ProductValueInterface $value
     *
     * @return string
     */
    protected function prepareOptionCode($data, ProductValueInterface $value)
    {
        return sprintf("%s.%s", $value->getAttribute()->getCode(), $data);
    }

    /**
     * Find Option entity from identifier
     *
     * @param string $identifier
     *
     * @return AttributeOption
     */
    protected function findEntity($identifier)
    {
        return $this->repository->findByReference($identifier);
    }
}
