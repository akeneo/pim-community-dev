<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Normalizer;

use Akeneo\Pim\Enrichment\Component\Error\DocumentedErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\IdentifiableDomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessageInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductDomainErrorNormalizer implements NormalizerInterface
{
    /**
     * @param IdentifiableDomainErrorInterface $object
     * @param string $format
     * @param array $context
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $data = [
            'type' => 'domain_error',
            'domain_error_identifier' => $object->getErrorIdentifier()
        ];

        if ($object instanceof \Throwable) {
            $data['message'] = $object->getMessage();
        }

        if ($object instanceof TemplatedErrorMessageInterface) {
            $data['message_template'] = $object->getMessageTemplate();
            $data['message_parameters'] = $object->getMessageParameters();
        }

        if ($object instanceof DocumentedErrorInterface) {
            $data['documentation'] = $object->getDocumentation();
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
            ];
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof IdentifiableDomainErrorInterface;
    }
}
