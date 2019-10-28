<?php

namespace Oro\Bundle\PimDataGridBundle\Normalizer\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataValueInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var ReferenceDataRepositoryResolverInterface */
    protected $repositoryResolver;

    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ReferenceDataRepositoryResolverInterface $repositoryResolver
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->repositoryResolver = $repositoryResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($referenceDataValue, $format = null, array $context = [])
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($referenceDataValue->getAttributeCode());

        if (null === $attribute) {
            return null;
        }

        $repository = $this->repositoryResolver->resolve($attribute->getReferenceDataName());
        $referenceData = $repository->findOneBy(['code' => $referenceDataValue->getData()]);

        if (null === $referenceData) {
            return null;
        }

        return [
            'locale' => $referenceDataValue->getLocaleCode(),
            'scope'  => $referenceDataValue->getScopeCode(),
            'data'   => $this->getReferenceDataLabel($referenceData),
        ];
    }

    /**
     *
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'datagrid' === $format && $data instanceof ReferenceDataValueInterface;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * Get the reference data label (or the [code] is no label is present).
     *
     * @param ReferenceDataInterface $referenceData
     *
     * @return string
     */
    protected function getReferenceDataLabel(ReferenceDataInterface $referenceData)
    {
        if (null !== $labelProperty = $referenceData::getLabelProperty()) {
            $getter = 'get' . ucfirst($labelProperty);
            $label = $referenceData->$getter();

            if (!empty($label)) {
                return $label;
            }
        }

        return sprintf('[%s]', $referenceData->getCode());
    }
}
