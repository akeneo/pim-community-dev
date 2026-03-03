<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddPriceCollectionCase implements AttributeCase
{
    public function getCase(): string
    {
        return "
                WHEN attribute.attribute_type = 'pim_catalog_price_collection' 
                    THEN CONCAT(
                            attribute.code,
                            '-',
                            (
                                SELECT GROUP_CONCAT(currency.code ORDER BY currency.code SEPARATOR '-')
                                FROM pim_catalog_channel channel
                                JOIN pim_catalog_channel_currency pccc ON channel.id = pccc.channel_id
                                JOIN pim_catalog_currency currency ON pccc.currency_id = currency.id
                                WHERE channel.code  = channel_code
                                GROUP BY channel.id
                            )
                        )
                ";
    }
}
