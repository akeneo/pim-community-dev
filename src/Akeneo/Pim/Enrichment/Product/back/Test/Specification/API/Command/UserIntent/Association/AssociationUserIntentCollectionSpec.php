<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationUserIntentCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([new AssociateProducts('X_SELL', ['identifier'])]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssociationUserIntentCollection::class);
        $this->shouldImplement(UserIntent::class);
    }

    function it_returns_the_association_user_intents()
    {
        $userIntent = new AssociateProducts('X_SELL', ['identifier']);
        $this->beConstructedWith([$userIntent]);
        $this->associationUserIntents()->shouldReturn([$userIntent]);
    }

    function it_cannot_be_instantiated_with_other_intent_than_association_intent()
    {
        $this->beConstructedWith([new SetTextValue('code', null, null, 'value')]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
