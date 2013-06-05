<?php

namespace Oro\Bundle\FormBundle\Tests\Functional;

class AppKernelTest extends WebTestCase
{
    public function testBoot()
    {
        $this->createClient();

        $container = self::$kernel->getContainer();
        $autocompleteConfig = $container->getParameter('oro_form.autocomplete.config');
        $this->assertEquals(
            array(
                'users' => array(
                    'type' => 'service',
                    'options' => array(
                        'service' => 'test_bundle.autocomplete.users_search_handler'
                    ),
                    'properties' => array(
                        array(
                            'name' => 'username'
                        )
                    ),
                    'entity_class' => 'Oro\\Bundle\\FormBundle\\Tests\\Functional\\TestBundle\\Entity\\User',
                    'form_options' => array(),
                    'route' => 'oro_form_autocomplete_search',
                    'view' => 'OroFormBundle:EntityAutocomplete:search.json.twig'
                )
            ),
            $autocompleteConfig
        );
    }
}
