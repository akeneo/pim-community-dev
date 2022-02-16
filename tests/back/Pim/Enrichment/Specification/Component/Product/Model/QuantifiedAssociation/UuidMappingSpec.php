<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\UuidMapping;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UuidMappingSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('createFromMapping', [['3f090f5e-3f54-4f34-879c-87779297d130' => 'product_identifier']]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UuidMapping::class);
    }

    public function it_is_created_from_a_mapping_and_returns_the_id_or_the_identifier()
    {
        $uuidAsStr = '3f090f5e-3f54-4f34-879c-87779297d130';
        $uuid = Uuid::fromString($uuidAsStr);
        $identifier = 'product_identifier';

        $this->beConstructedThrough('createFromMapping', [[$uuidAsStr => $identifier]]);

        $this->getIdentifier(Uuid::fromString($uuid))->shouldReturn($identifier);
        $this->getUuid($identifier)->equals($uuid)->shouldBe(true);
        $this->hasUuid($identifier)->shouldReturn(true);
        $this->hasIdentifier($uuid)->shouldReturn(true);
        $this->hasUuid('nice')->shouldReturn(false);
        $this->hasIdentifier(Uuid::fromString('52254bba-a2c8-40bb-abe1-195e3970bd93'))->shouldReturn(false);
    }

    public function it_throws_if_the_product_id_is_not_an_integer()
    {
        $invalidUuid = 'wrong_uuid';
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('createFromMapping', [[$invalidUuid => 'product_identifier']]);
    }

    public function it_throws_if_the_identifier_is_not_an_non_empty_string()
    {
        $invalidIdentifier = '';
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('createFromMapping', [['3f090f5e-3f54-4f34-879c-87779297d130' => $invalidIdentifier]]);
    }
}
