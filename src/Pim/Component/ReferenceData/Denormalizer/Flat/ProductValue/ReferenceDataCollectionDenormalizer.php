<?php

namespace Pim\Component\ReferenceData\Denormalizer\Flat\ProductValue;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataCollectionDenormalizer implements DenormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['csv'];

    /** @var string[] */
    protected $supportedTypes;

    /** @var ReferenceDataRepositoryResolverInterface */
    protected $repositoryResolver;

    /**
     * @param array                                    $supportedTypes
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
        $collection = new ArrayCollection();
        if (null === $this->repositoryResolver || empty($data)) {
            return $collection;
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

        $codes = explode(',', $data);
        foreach ($codes as $code) {
            $referenceData = $repository->findOneBy(['code' => trim($code)]);

            if (null !== $referenceData) {
                $collection->add($referenceData);
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array($type, $this->supportedTypes) && in_array($format, $this->supportedFormats);
    }
}
