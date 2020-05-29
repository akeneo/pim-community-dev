<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdMappingSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('createFromMapping', [[1 => 'product_identifier']]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IdMapping::class);
    }

    public function it_is_created_from_a_mapping_and_returns_the_id_or_the_identifier()
    {
        $id = 1;
        $identifier = 'product_identifier';

        $this->beConstructedThrough('createFromMapping', [[$id => $identifier]]);

        $this->getIdentifier($id)->shouldReturn($identifier);
        $this->getId($identifier)->shouldReturn($id);
        $this->hasId($identifier)->shouldReturn(true);
        $this->hasIdentifier($id)->shouldReturn(true);
        $this->hasId('nice')->shouldReturn(false);
        $this->hasIdentifier(12)->shouldReturn(false);
    }

    public function it_throws_if_the_product_id_is_not_an_integer()
    {
        $invalidId = 'wrong_id';
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('createFromMapping', [[$invalidId => 'product_identifier']]);
    }

    public function it_throws_if_the_identifier_is_not_an_non_empty_string()
    {
        $invalidIdentifier = '';
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('createFromMapping', [[1 => $invalidIdentifier]]);
    }
}
