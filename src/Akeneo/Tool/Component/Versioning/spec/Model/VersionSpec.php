<?php

namespace spec\Akeneo\Tool\Component\Versioning\Model;

use Akeneo\Tool\Component\Versioning\Model\Version;
use PhpSpec\ObjectBehavior;

class VersionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('JobInstance', 1537, 'Julia', 'import');
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
