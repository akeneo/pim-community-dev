<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataDenormalizer extends AbstractValueDenormalizer
{
    /** @var ReferenceDataRepositoryResolverInterface  */
    protected $repositoryResolver;

    /**
     * @param array                                    $supportedTypes
     * @param ReferenceDataRepositoryResolverInterface $repositoryResolver
     */
    public function __construct(
        array $supportedTypes,
        ReferenceDataRepositoryResolverInterface $repositoryResolver = null
    ) {
        parent::__construct($supportedTypes);

        $this->repositoryResolver = $repositoryResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $referenceDataClass, $format = null, array $context = array())
    {
        if (null === $this->repositoryResolver || empty($data)) {
            return null;
        }

        if (!$context['value'] instanceof ProductValueInterface) {
            throw new InvalidParameterException(
                'Value is not an instance of Pim\Bundle\CatalogBundle\Model\ProductValueInterface.'
            );
        }

        $attribute = $context['value']->getAttribute();
        if (null === $attribute) {
            throw new InvalidParameterException(
                'Denormalizer\'s context expected to have an attribute, none found.'
            );
        }

        $repository = $this->repositoryResolver->resolve($attribute->getReferenceDataName());

        return $repository->findOneBy(['code' => $data]);
    }
}
