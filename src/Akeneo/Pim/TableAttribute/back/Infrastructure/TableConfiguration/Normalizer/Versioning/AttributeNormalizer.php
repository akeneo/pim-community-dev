<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Normalizer\Versioning;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private NormalizerInterface $baseNormalizer;
    private SelectOptionCollectionRepository $selectOptionCollectionRepository;

    public function __construct(
        NormalizerInterface $baseNormalizer,
        SelectOptionCollectionRepository $selectOptionCollectionRepository
    ) {
        $this->baseNormalizer = $baseNormalizer;
        $this->selectOptionCollectionRepository = $selectOptionCollectionRepository;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        $normalized = $this->baseNormalizer->normalize($object, $format, $context);
        if ($object instanceof AttributeInterface && $object->getType() === AttributeTypes::TABLE) {
            $configuration = $object->getRawTableConfiguration();
            foreach ($configuration as $index => $columnDefinition) {
                if (SelectColumn::DATATYPE === ($columnDefinition['data_type'] ?? null)) {
                    $options = $columnDefinition['options'] ?? null;
                    if (null === $options) {
                        $options = $this->selectOptionCollectionRepository->getByColumn(
                            $object->getCode(),
                            ColumnCode::fromString($columnDefinition['code'])
                        );
                        $options = $options->normalize();
                    }
                    foreach ($options as $optionIndex => $option) {
                        unset($options[$optionIndex]['labels']);
                    }
                    $configuration[$index]['options'] = $options;
                }
            }
            $normalized['table_configuration'] = \json_encode($configuration);
        }

        return $normalized;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $this->baseNormalizer->supportsNormalization($data, $format);
    }
}
