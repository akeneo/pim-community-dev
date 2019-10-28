<?php

namespace Oro\Bundle\PimDataGridBundle\Normalizer\Product;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\PresenterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Normalizer for a collection of product values
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValuesNormalizer implements NormalizerInterface, SerializerAwareInterface, CacheableSupportsMethodInterface
{
    use SerializerAwareTrait;

    /** @var PresenterRegistryInterface */
    protected $presenterRegistry;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param PresenterRegistryInterface $presenterRegistry
     * @param UserContext                $userContext
     */
    public function __construct(PresenterRegistryInterface $presenterRegistry, UserContext $userContext)
    {
        $this->presenterRegistry = $presenterRegistry;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($data, $format = null, array $context = [])
    {
        $result = [];

        foreach ($data as $value) {
            $normalizedValue = $this->serializer->normalize($value, $format, $context);

            $attributeCode = $value->getAttributeCode();
            $presenter = $this->presenterRegistry->getPresenterByAttributeCode($attributeCode);
            if (null !== $presenter) {
                $normalizedValue['data'] = $presenter->present($normalizedValue['data'], [
                    'locale' => $this->userContext->getUiLocaleCode()
                ]);
            }
            $result[$attributeCode][] = $normalizedValue;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'datagrid' === $format && $data instanceof WriteValueCollection;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
