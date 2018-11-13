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

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer\ConnectorValueTransformerInterface;
use PhpSpec\ObjectBehavior;

class RecordConnectorValueTransformerSpec extends ObjectBehavior
{
    function it_is_a_connector_value_transformer()
    {
        $this->shouldImplement(ConnectorValueTransformerInterface::class);
    }

    function it_only_supports_a_value_of_an_record_attribute(
        TextAttribute $textAttribute,
        RecordAttribute $recordAttribute
    ) {
        $this->supports($recordAttribute)->shouldReturn(true);
        $this->supports($textAttribute)->shouldReturn(false);
    }

    function it_transforms_a_normalized_value_without_missing_data_to_a_normalized_connector_value()
    {
        $this->transform([
            'data'      => 'france',
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'country_designer_fingerprint',
        ])->shouldReturn([
            'locale'  => 'en_us',
            'channel' => 'ecommerce',
            'data'    => 'france',
        ]);
    }

    function it_transforms_a_normalized_value_with_missing_data_to_a_normalized_connector_value()
    {
        $this->transform([
            'attribute' => 'country_designer_fingerprint',
        ])->shouldReturn([
            'locale'  => null,
            'channel' => null,
            'data'    => null,
        ]);
    }
}
