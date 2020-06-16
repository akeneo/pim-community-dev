<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Normalizer;

use Akeneo\Pim\Enrichment\Component\Error\Documented\DocumentedErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductDomainErrorNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @param DomainErrorInterface $object
     * @param string $format
     * @param array $context
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $data = [
            'type' => 'domain_error'
        ];

        if ($object instanceof \Exception) {
            $data['message'] = $object->getMessage();
        }

        if ($object instanceof TemplatedErrorMessageInterface) {
            $data['message_template'] = $object->getTemplatedErrorMessage()->getTemplate();
            $data['message_parameters'] = $object->getTemplatedErrorMessage()->getParameters();
        }

        if ($object instanceof DocumentedErrorInterface) {
            $data['documentation'] = $object->getDocumentation()->normalize();
        }

        if (isset($context['product'])) {
            $product = $context['product'];
            if (false === $product instanceof ProductInterface) {
                throw new \LogicException(
                    sprintf('Context property "product" should be an instance of %s', ProductInterface::class)
                );
            }

            $data['product'] = [
                'id' => $product->getId(),
                'identifier' => $product->getIdentifier(),
                'label' => $product->getLabel(),
                'family' => null !== $product->getFamily() ? $product->getFamily()->getCode() : null,
            ];
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof DomainErrorInterface;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
