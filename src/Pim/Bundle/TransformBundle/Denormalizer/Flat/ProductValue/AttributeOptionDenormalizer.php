<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue;

use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeOptionRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Attribute option flat denormalizer used for following attribute types:
 * - pim_catalog_simpleselect
 *
 * @author    Romain Monceau <romain@akeneo.com>
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
        if ($data === null || $data === '') {
            return null;
        }

        $resolver = new OptionsResolver();
        $this->configContext($resolver);
        $context = $resolver->resolve($context);

        $value = $context['value'];

        $option = $this->findEntity(
            $this->prepareOptionCode($data, $value)
        );

        return $option;
    }

    /**
     * Prepare option code for AttributeOptionRepository::findByReference
     *
     * @param string                $data
     * @param ProductValueInterface $value
     *
     * @return string
     *
     * @deprecated AttributeOptionRepository::findByReference should take a code as parameter
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
     * @return AttributeOptionInterface
     */
    protected function findEntity($identifier)
    {
        return $this->repository->findOneByIdentifier($identifier);
    }

    /**
     * Define context requirements
     *
     * @param OptionsResolver $resolver
     */
    protected function configContext(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['value'])
            ->setDefined(['entity', 'locale_code', 'product', 'scope_code']);
    }
}
