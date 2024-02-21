<?php

namespace spec\Akeneo\Tool\Component\Versioning\Model;

use Akeneo\Tool\Component\Versioning\Model\Version;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class VersionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('JobInstance', 1537, null, 'Julia', 'import');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Version::class);
    }

    function it_has_an_id()
    {
        $this->getId()->shouldBe(null);
        $this->setId(1);
        $this->getId()->shouldBe(1);
    }

    function it_has_an_author()
    {
        $this->getAuthor()->shouldBe('Julia');
    }

    function it_has_a_resource_id()
    {
        $this->getResourceId()->shouldBe(1537);
    }

    function it_has_no_resource_uuid()
    {
        $this->getResourceUuid()->shouldBeNull();
    }

    function it_can_be_constructed_with_a_uuid()
    {
        $uuid = Uuid::fromString('114c9108-444d-408a-ab43-195068166d2c');
        $this->beConstructedWith('JobInstance', null, $uuid, 'Julia', 'import');
        $this->getResourceId()->shouldBeNull();
        $this->getResourceUuid()->shouldReturn($uuid);
    }

    function it_has_a_resource_name()
    {
        $this->getResourceName()->shouldBe('JobInstance');
    }

    function it_has_a_version()
    {
        $this->getVersion()->shouldBe(null);
        $this->setVersion(1);
        $this->getVersion()->shouldBe(1);
    }

    function it_has_a_snapshot()
    {
        $this->getSnapshot()->shouldBe(null);
        $this->setSnapshot(['field' => 'foo']);
        $this->getSnapshot()->shouldBe(['field' => 'foo']);
    }

    function it_has_a_changeset()
    {
        $this->getChangeset()->shouldBe(null);
        $this->setChangeset(['field' => 'foo']);
        $this->getChangeset()->shouldBe(['field' => 'foo']);
    }

    function it_has_a_context()
    {
        $this->getContext()->shouldBe('import');
    }

    function it_stores_date_of_getting_logged()
    {
        $this->getLoggedAt()->shouldHaveType('\DateTime');
    }

    function it_can_have_a_pending_state()
    {
        $this->isPending()->shouldBe(true);
        $this->setSnapshot(['field' => 'foo']);
        $this->isPending()->shouldBe(false);
    }
}
