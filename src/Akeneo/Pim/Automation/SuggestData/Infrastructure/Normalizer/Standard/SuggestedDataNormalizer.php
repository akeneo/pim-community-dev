<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Normalizer\Standard;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SuggestedDataNormalizer
{
    /** @var ArrayConverterInterface */
    private $valueConverter;

    /**
     * @param ArrayConverterInterface $valueConverter
     */
    public function __construct(ArrayConverterInterface $valueConverter)
    {
        $this->valueConverter = $valueConverter;
    }

    /**
     * @param SuggestedData $suggestedData
     *
     * @return array
     */
    public function normalize(SuggestedData $suggestedData): array
    {
        $normalizedValues = $this->valueConverter->convert($suggestedData->getValues());

        foreach ($normalizedValues as $code => $value) {
            foreach ($value as $data) {
                if (null !== $data['scope'] || null !== $data['locale']) {
                    unset($normalizedValues[$code]);
                    break;
                }
            }
        }

        return $normalizedValues;
    }
}
