<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Api\Normalizer\Exception\ViolationNormalizer;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Normalize a ViolationHttpException with all errors
 *
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConstraintViolationNormalizer extends ViolationNormalizer
{
    /**
     * @param ConstraintViolationInterface $object
     * @param string $format
     * @param array $context
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $data = parent::normalizeViolation($object);
        unset($data['_key']);

        $data['type'] = 'violation_error';
        $data['message_template'] = $object->getMessageTemplate();
        $data['message_parameters'] = $object->getParameters();

        if (isset($context['product'])) {
            $product = $context['product'];
            if (false === $product instanceof ProductInterface) {
                throw new \LogicException(
                    sprintf('Context property "product" should be an instance of %s', ProductInterface::class)
                );
            }

            if (null !== $product->getId()) {
                $data['product'] = [
                    'id' => $product->getId(),
                    'identifier' => $product->getIdentifier(),
                    'label' => $product->getLabel(),
                    'family' => null !== $product->getFamily() ? $product->getFamily()->getCode() : null,
                ];
            }


        }

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ConstraintViolationInterface;
    }
}
