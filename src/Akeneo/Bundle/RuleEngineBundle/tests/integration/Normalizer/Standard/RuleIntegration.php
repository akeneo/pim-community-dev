<?php

namespace tests\integration\Akeneo\Bundle\RuleEngineBundle\Normalizer\Standard;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class RuleIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        $rootPath = $this->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        return new Configuration(
            [
                Configuration::getTechnicalCatalogPath(),
                $rootPath . 'tests' . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'technical'
            ]
        );
    }

    public function testRule()
    {
        $expected = [
            'code'       => 'set_localized_scopable_text',
            'type'       => 'product',
            'priority'   => 10,
            'conditions' => [
                [
                    'field'    => 'categories',
                    'operator' => 'IN',
                    'value'    => 'master'
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
