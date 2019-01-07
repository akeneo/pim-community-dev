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

namespace spec\Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttributeOption;
use PhpSpec\ObjectBehavior;

class ConnectorAttributeOptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            OptionCode::fromString('attribute_option'),
            LabelCollection::fromArray([
                'en_US' => 'Option',
                'fr_FR' => 'Optionne'
            ])
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConnectorAttributeOption::class);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([
            'code' => 'attribute_option',
            'labels' => [
                'en_US' => 'Option',
                'fr_FR' => 'Optionne'
            ]
        ]);
    }
}
