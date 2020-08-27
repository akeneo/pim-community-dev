<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering;

use PhpSpec\ObjectBehavior;

class DiffFactorySpec extends ObjectBehavior
{
    function it_creates_diff_instance()
    {
        $diff = $this->create('foo', 'bar');

        $diff->getA()->shouldBe(['foo']);
        $diff->getB()->shouldBe(['bar']);
        $diff->getGroupedOpcodes()->shouldBe([
            [
                ['replace', 0, 1, 0, 1]
            ]
        ]);

        $diff = $this->create(['foo', 'bar', 'moo'], ['bar', 'moo']);

        $diff->getA()->shouldBe(['foo', 'bar', 'moo']);
        $diff->getB()->shouldBe(['bar', 'moo']);
        $diff->getGroupedOpcodes()->shouldBe([
            [
                ['delete', 0, 1, 0, 0],
                ['equal', 1, 3, 0, 2]
            ]
        ]);

    }

    function it_creates_diff_instance_with_empty_value()
    {
        $before = [];
        $after = ['$10.00'];

        $diff = $this->create($before, $after);

        $diff->getA()->shouldBe($before);
        $diff->getB()->shouldBe($after);

        /*
         * Workaround: we add "@" before the call to render to avoid warning in dev environment because of
         * the methods Diff_SequenceMatcher::setSeq1 and Diff_SequenceMatcher::setSeq2.
         * Both methods don't use the strict comparison (===), so an empty array equals to null.
         */
        @$diff->getGroupedOpcodes()->shouldBe([
            [
                ['insert', 0, 0, 0, 1]
            ]
        ]);


        $before = ['$10.00'];
        $after = [];

        $diff = $this->create($before, $after);

        $diff->getA()->shouldBe($before);
        $diff->getB()->shouldBe($after);
        /*
         * Workaround: we add "@" before the call to render to avoid warning in dev environment because of
         * the methods Diff_SequenceMatcher::setSeq1 and Diff_SequenceMatcher::setSeq2.
         * Both methods don't use the strict comparison (===), so an empty array equals to null.
         */
        @$diff->getGroupedOpcodes()->shouldBe([
            [
                ['delete', 0, 1, 0, 0]
            ]
        ]);
    }
}
