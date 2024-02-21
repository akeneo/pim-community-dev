<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\Normalizer;

use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Api\Normalizer\Exception\ViolationNormalizer;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
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
    /** @var DocumentationBuilderRegistry */
    private $documentationBuilderRegistry;

    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        DocumentationBuilderRegistry $documentationBuilderRegistry
    ) {
        parent::__construct($attributeRepository);

        $this->documentationBuilderRegistry = $documentationBuilderRegistry;
    }

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

        if (null !== $documentation = $this->documentationBuilderRegistry->getDocumentation($object)) {
            $data['documentation'] = $documentation->normalize();
        }

        if (isset($context['product'])) {
            $product = $context['product'];
            if (false === $product instanceof ProductInterface) {
                throw new \LogicException(
                    sprintf('Context property "product" should be an instance of %s', ProductInterface::class)
                );
            }
            $data['product'] = [
                'uuid' => $product->getUuid()->toString(),
                'identifier' => $product->getIdentifier(),
                'label' => $product->getLabel(),
                'family' => null !== $product->getFamily() ? $product->getFamily()->getCode() : null,
            ];
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ConstraintViolationInterface;
    }
}
