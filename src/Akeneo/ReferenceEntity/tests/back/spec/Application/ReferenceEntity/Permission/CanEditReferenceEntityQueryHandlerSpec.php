<?php

namespace spec\Akeneo\ReferenceEntity\Application\ReferenceEntity\Permission;

use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQuery;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQueryHandler;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\SecurityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\CanEditReferenceEntityInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CanEditReferenceEntityQueryHandlerSpec extends ObjectBehavior
{
    public function let(CanEditReferenceEntityInterface $canEditReferenceEntity)
    {
        $this->beConstructedWith($canEditReferenceEntity);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CanEditReferenceEntityQueryHandler::class);
    }

    function it_tells_if_a_user_is_allowed_to_edit_a_reference_entity($canEditReferenceEntity)
    {
        $query = new CanEditReferenceEntityQuery();
        $query->referenceEntityIdentifier = 'brand';
        $query->securityIdentifier = 'julia';
        $canEditReferenceEntity->__invoke(
            Argument::type(SecurityIdentifier::class),
            Argument::type(ReferenceEntityIdentifier::class)
        )->willReturn(true);

        $this->__invoke($query)->shouldReturn(true);

    }
}
