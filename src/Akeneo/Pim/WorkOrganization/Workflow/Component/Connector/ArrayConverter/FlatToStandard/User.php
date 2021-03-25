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

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;

class User implements ArrayConverterInterface
{
    private ArrayConverterInterface $baseConverter;

    public function __construct(ArrayConverterInterface $baseConverter)
    {
        $this->baseConverter = $baseConverter;
    }

    public function convert(array $item, array $options = []): array
    {
        $keysToConvert = ['proposals_state_notifications', 'proposals_to_review_notification'];
        foreach ($keysToConvert as $keyToConvert) {
            if (\array_key_exists($keyToConvert, $item)) {
                $item[$keyToConvert] = '1' === $item[$keyToConvert];
            }
        }

        return $this->baseConverter->convert($item, $options);
    }
}
