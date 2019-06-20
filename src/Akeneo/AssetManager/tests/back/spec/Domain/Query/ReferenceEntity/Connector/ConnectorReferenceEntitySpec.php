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

namespace spec\Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\ConnectorReferenceEntity;
use PhpSpec\ObjectBehavior;

class ConnectorReferenceEntitySpec extends ObjectBehavior
{
    function let()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('starck');
        $labelCollection = LabelCollection::fromArray([
            'en_US' => 'Stark',
            'fr_FR' => 'Stark'
        ]);

        $this->beConstructedWith(
            $referenceEntityIdentifier,
            $labelCollection,
            Image::createEmpty()
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConnectorReferenceEntity::class);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([
            'code' => 'starck',
            'labels'                   => [
                'en_US' => 'Stark',
                'fr_FR' => 'Stark',
            ],
            'image' => null,
        ]);
    }
}
