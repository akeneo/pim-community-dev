<?php

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;

/**
 * Normalizes an IdentifiersMapping for API
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IdentifiersMappingNormalizer
{
    /**
     * @param IdentifiersMapping $mapping
     *
     * @return array
     */
    public function normalize(IdentifiersMapping $mapping): array
    {
        $result = [];

        foreach ($mapping->getIdentifiers() as $identifier => $attribute) {
            $attribute->setLocale('en_US');
            $result[$identifier] = [
                'code' => $attribute->getCode(),
                'label' => [
                    'en_us' => $attribute->getLabel()
                ]
            ];
        }

        return $result;
    }
}
