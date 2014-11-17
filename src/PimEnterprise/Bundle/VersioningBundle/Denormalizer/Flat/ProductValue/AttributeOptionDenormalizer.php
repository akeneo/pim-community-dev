<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Attribute option flat denormalizer used for following attribute types:
 * - pim_catalog_simpleselect
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeOptionDenormalizer extends AbstractValueDenormalizer
{
    /** @var AttributeOptionRepository */
    protected $repository;

    /**
     * @param string[]                  $supportedTypes
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
     * @return \Pim\Bundle\CatalogBundle\Entity\AttributeOption
     */
    protected function findEntity($identifier)
    {
        return $this->repository->findByReference($identifier);
    }

    /**
     * Define context requirements
     * @param OptionsResolverInterface $resolver
     */
    protected function configContext(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['value']);
    }
}
