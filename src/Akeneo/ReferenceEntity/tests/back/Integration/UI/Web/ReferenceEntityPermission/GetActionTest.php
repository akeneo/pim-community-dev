<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\UI\Web\ReferenceEntityPermission;

use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class GetActionTest extends ControllerIntegrationTestCase
{
    private const RESPONSES_DIR = 'ReferenceEntityPermission/';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp()
    {
        parent::setUp();

        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), $this->testKernel))
            ->logIn('julia');
        $this->webClientHelper = $this->get('akeneoreference_entity.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_shows_the_list_of_permissions_for_an_enriched_entity()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'show.json');
    }

    /**
     * @test
     */
    public function it_shows_an_empty_list_of_permissions()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'show_empty.json');
    }
}
