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

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

class TableValueNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private NormalizerInterface $baseTableNormalizer;

    public function __construct(NormalizerInterface $baseTableNormalizer)
    {
        $this->baseTableNormalizer = $baseTableNormalizer;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        Assert::isInstanceOf($object, ValueInterface::class);
        Assert::isInstanceOf($object->getData(), Table::class);

        if ($context['is_workflow'] ?? false) {
            return [
                \sprintf('%s-table', $object->getAttributeCode()) => [
                    $object->getScopeCode() ?? '<all_channels>' => [
                        $object->getLocaleCode() ?? '<all_locales>' => 'not_empty',
                    ]
                ]
            ];
        }

        return $this->baseTableNormalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $this->baseTableNormalizer->supportsNormalization($data, $format);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
