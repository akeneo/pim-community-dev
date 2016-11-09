<?php

namespace tests\integration\Akeneo\Bundle\RuleEngineBundle\Normalizer\Standard;

use TestEnterprise\Integration\TestCase;

class RuleIntegration extends TestCase
{
    public function testRule()
    {
        $expected = [
            'code'       => 'set_localized_scopable_text',
            'type'       => 'product',
            'priority'   => 10,
            'conditions' => [
                [
                    'field' => 'categories.code',
                    'operator' => 'IN',
                    'value' => 'master'
                ]
            ],
            'actions'    => [
                [
                    'type'   => 'set',
                    'field'  => 'a_localized_and_scopable_text_area',
                    'value'  => 'an other text',
                    'locale' => 'en_US',
                    'scope'  => 'tablet',
                ]
            ],
        ];

        $repository = $this->get('akeneo_rule_engine.repository.rule_definition');
        $serializer = $this->get('pim_serializer');

        // TODO: uncomment it when old normalizer will be removed
        //$result = $serializer->normalize($repository->findOneByIdentifier('set_localized_scopable_text'), 'standard');
        //$this->assertSame($result, $expected);
    }
}
