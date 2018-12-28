<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\UI\Web\ReferenceEntityPermission;

use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class SetActionTest extends ControllerIntegrationTestCase
{
    private const REFERENCE_ENTITY_PERMISSION_SET_ROUTE = 'akeneo_reference_entities_reference_entity_permission_set_rest';
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
    public function it_sets_user_group_permission_on_a_reference_entity()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'ok.json');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_none_of_the_user_groups_have_edit_permission_on_the_reference_entity()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'no_edit_permission.json');
    }
}
