<?php

declare(strict_types=1);

namespace Akeneo\Channel\Component\Query\PublicApi;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetChannelCodeWithLocaleCodesInterface
{
    /**
     * Returns a list of channel codes with bound locales codes using this format:
     *
     *  [
     *      {
     *          "channelCode": "ecommerce",
     *          "localeCodes": ["en_US", "fr_FR"]
     *      },
     *      {
     *          "channelCode": "mobile",
     *          "localeCodes": ["en_US", "de_DE"]
     *      }
     *  ]
     *
     * @return array
     */
    public function findAll(): array;
}
