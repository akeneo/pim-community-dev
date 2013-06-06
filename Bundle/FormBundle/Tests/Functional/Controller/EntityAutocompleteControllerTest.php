<?php

namespace Oro\Bundle\FormBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;
use Oro\Bundle\FormBundle\Tests\Functional\WebTestCase;

class EntityAutocompleteControllerTest extends WebTestCase
{
    public function testSearchAction()
    {
        $client = $this->createClient();

        $client->request('GET', '/autocomplete/search?name=users&per_page=3');

        /** @var Response $response */
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            array(
                'results' => array(
                    array('id' => 1, 'text' => 'User #1'),
                    array('id' => 2, 'text' => 'User #2'),
                    array('id' => 3, 'text' => 'User #3')
                ),
                'more' => true
            ),
            json_decode($response->getContent(), true)
        );
    }
}
