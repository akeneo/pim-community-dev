<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeOptionRepositoryInterface;

/**
 * Attribute option denormalizer used for following attribute types:
 * - pim_catalog_simpleselect
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionDenormalizer extends AbstractValueDenormalizer
{
    /** @var AttributeOptionRepositoryInterface */
    protected $repository;

    /**
     * @param string[]                           $supportedTypes
     * @param AttributeOptionRepositoryInterface $repository
     */
    public function __construct(array $supportedTypes, AttributeOptionRepositoryInterface $repository)
    {
        parent::__construct($supportedTypes);

        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (null === $data || '' === $data) {
            return null;
        }

        $attribute = $context['attribute'];

        $code = $this->prepareOptionCode($attribute, $data);

        return $this->repository->findOneByIdentifier($code);
    }

    /**
     * Prepare option code for AttributeOptionRepository::findByReference
     *
     * @param AttributeInterface $attribute
     * @param string             $optionCode
     *
     * @return string
     *
     * @deprecated AttributeOptionRepository::findByReference should take a code as parameter
     */
    protected function prepareOptionCode(AttributeInterface $attribute, $optionCode)
    {
        return sprintf('%s.%s', $attribute->getCode(), $optionCode);
    }
}
