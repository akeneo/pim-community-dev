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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\ExternalApi;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

final class TableAttributeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private NormalizerInterface $normalizer;
    private SelectOptionCollectionRepository $optionCollectionRepository;

    public function __construct(
        NormalizerInterface $normalizer,
        SelectOptionCollectionRepository $optionCollectionRepository
    ) {
        $this->normalizer = $normalizer;
        $this->optionCollectionRepository = $optionCollectionRepository;
    }

    /**
     * @param AttributeInterface $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        Assert::isInstanceOf($object, AttributeInterface::class);
        $withTableSelectOptions = $context['with_table_select_options'] ?? false;

        $normalized = $this->normalizer->normalize($object, $format, $context);
        if (!$withTableSelectOptions
            || $object->getType() !== AttributeTypes::TABLE
            || !is_array($normalized['table_configuration'] ?? null)
        ) {
            return $normalized;
        }

        foreach ($normalized['table_configuration'] as $index => $normalizedColumn) {
            if ($normalizedColumn['data_type'] !== SelectColumn::DATATYPE) {
                continue;
            }

            $optionCollection = $this->optionCollectionRepository->getByColumn(
                $object->getCode(),
                ColumnCode::fromString($normalizedColumn['code'])
            );
            $normalized['table_configuration'][$index]['options'] = $optionCollection->normalize();
        }

        return $normalized;
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof AttributeInterface && $format === 'external_api';
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
