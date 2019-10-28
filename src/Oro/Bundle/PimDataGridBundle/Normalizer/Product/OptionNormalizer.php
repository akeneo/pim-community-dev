<?php

namespace Oro\Bundle\PimDataGridBundle\Normalizer\Product;

use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeOptionRepository;

    public function __construct(IdentifiableObjectRepositoryInterface $attributeOptionRepository)
    {
        $this->attributeOptionRepository = $attributeOptionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($optionValue, $format = null, array $context = [])
    {
        $optionCode = $optionValue->getData();
        $attributeCode = $optionValue->getAttributeCode();

        $option = $this->attributeOptionRepository->findOneByIdentifier($attributeCode.'.'.$optionCode);

        $label = '';
        if ($option instanceof AttributeOptionInterface) {
            if (isset($context['data_locale'])) {
                $option->setLocale($context['data_locale']);
            }
            $translation = $option->getTranslation();

            $label = null !== $translation->getValue() ?
                $translation->getValue() :
                sprintf('[%s]', $optionCode);
        }

        return [
            'locale' => $optionValue->getLocaleCode(),
            'scope'  => $optionValue->getScopeCode(),
            'data'   => $label
        ];
    }

    /**
     *
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'datagrid' === $format && $data instanceof OptionValueInterface;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
