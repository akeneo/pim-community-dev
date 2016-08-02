<?php

namespace Pim\Component\ReferenceData\Denormalizer\Flat\ProductValue;

use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataDenormalizer implements DenormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['csv'];

    /** @var string[] */
    protected $supportedTypes;

    /** @var ReferenceDataRepositoryResolverInterface */
    protected $repositoryResolver;

    /**
     * @param string[]                                 $supportedTypes
     * @param ReferenceDataRepositoryResolverInterface $repositoryResolver
     */
    public function __construct(
        array $supportedTypes,
        ReferenceDataRepositoryResolverInterface $repositoryResolver = null
    ) {
        $this->supportedTypes = $supportedTypes;
        $this->repositoryResolver = $repositoryResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $referenceDataClass, $format = null, array $context = [])
    {
        if (null === $this->repositoryResolver || empty($data)) {
            return null;
        }

        if (!$context['value'] instanceof ProductValueInterface) {
            throw new \InvalidArgumentException(
                'Value is not an instance of Pim\Component\Catalog\Model\ProductValueInterface.'
            );
        }

        $attribute = $context['value']->getAttribute();
        if (null === $attribute) {
            throw new \InvalidArgumentException(
                'Denormalizer\'s context expected to have an attribute, none found.'
            );
        }

        $repository = $this->repositoryResolver->resolve($attribute->getReferenceDataName());

        return $repository->findOneBy(['code' => $data]);
    }
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array($type, $this->supportedTypes) && in_array($format, $this->supportedFormats);
    }
}
