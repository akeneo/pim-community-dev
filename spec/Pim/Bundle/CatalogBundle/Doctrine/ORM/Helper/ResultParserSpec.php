<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Helper;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResultParserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\ORM\Helper\ResultParser');
    }

    function it_parses_translations()
    {
        $this::parseTranslations(
            [
                ['id' => 10, 'label' => 'group fr', 'code' => 'group_code', 'locale' => 'fr_FR'],
                ['id' => 10, 'label' => 'group en', 'code' => 'group_code', 'locale' => 'en_US'],
                ['id' => 11, 'label' => null, 'code' => 'group_other_code', 'locale' => 'fr_FR'],
            ],
            'en_US'
        )->shouldReturn([
            10 => 'group en',
            11 => '[group_other_code]',
        ]);
    }

    function it_parses_ids()
    {
        $this::parseIds([
            ['id' => 10],
            ['id' => 101],
            ['id' => 11],
        ])->shouldReturn([
            10,
            101,
            11,
        ]);
    }
}
