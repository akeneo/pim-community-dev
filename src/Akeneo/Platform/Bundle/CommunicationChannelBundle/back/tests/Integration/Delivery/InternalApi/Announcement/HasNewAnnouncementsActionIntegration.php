<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Test\Integration\Delivery\InternalApi\Announcement;

use Akeneo\Platform\CommunicationChannel\Test\Integration\WebTestCase;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class HasNewAnnouncementsActionIntegration extends WebTestCase
{
    public function setUp(): void
    {
        parent::setup();

        $this->authenticateAsAdmin();
    }

    public function test_it_can_respond_if_it_has_new_announcements_or_not()
    {
        $this->client->request(
            'GET',
            '/rest/new_announcements'
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertArrayHasKey('status', $content);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
