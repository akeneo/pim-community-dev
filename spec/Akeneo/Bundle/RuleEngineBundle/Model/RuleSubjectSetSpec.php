<?php

namespace spec\Akeneo\Bundle\RuleEngineBundle\Model;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RuleSubjectSetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSet');
    }

    function it_skips_a_subject(RuleInterface $nin, RuleInterface $ratm, RuleInterface $bieber, RuleInterface $tool)
    {
        $subjects = [$nin, $ratm, $bieber, $tool];
        $this->setSubjects($subjects);

        $this->skipSubject($bieber, ['Do we really need a reason ?', 'Ok... Because !']);

        $this->getSubjects()->shouldReturn([0 => $nin, 1 => $ratm, 3 => $tool]);
        $this->getSkippedSubjects()->shouldReturn(
            [
                ['subject' => $bieber, 'reasons' => ['Do we really need a reason ?', 'Ok... Because !']],
            ]
        );
    }

    function it_tells_if_a_subject_is_skipped(RuleInterface $nin, RuleInterface $ratm, RuleInterface $bieber, RuleInterface $tool)
    {
        $subjects = [$nin, $ratm, $bieber, $tool];
        $this->setSubjects($subjects);

        $this->skipSubject($bieber, ['Do we really need a reason ?', 'Ok... Because !']);

        $this->isSkipped($bieber)->shouldReturn(true);
        $this->isSkipped($nin)->shouldReturn(false);
    }

    function it_gives_the_reasons_of_a_skip(RuleInterface $nin, RuleInterface $ratm, RuleInterface $bieber, RuleInterface $tool)
    {
        $subjects = [$nin, $ratm, $bieber, $tool];
        $this->setSubjects($subjects);

        $this->skipSubject($bieber, ['Do we really need a reason ?', 'Ok... Because !']);

        $this->getSkippedReasons($bieber)->shouldReturn(['Do we really need a reason ?', 'Ok... Because !']);
        $this->getSkippedReasons($nin)->shouldReturn([]);
    }
}
