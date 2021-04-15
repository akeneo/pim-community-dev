<?php

namespace Akeneo\Connectivity\Connection\Tests\Integration\Validation;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WriteConnectionWebhookValidationIntegration extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->get('validator');

        /** @var ConnectionLoader $connectionLoader */
        $connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION, false);
    }

    public function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_the_url_is_valid_when_has_255_characters()
    {
        $url = $this->createValidUrlOfLength(255);

        $value = new ConnectionWebhook('magento', true, $url);
        $errors = $this->validator->validate($value);

        $this->assertCount(0, $errors);
    }

    public function test_the_url_is_invalid_when_more_than_255_characters()
    {
        $url = $this->createValidUrlOfLength(256);

        $value = new ConnectionWebhook('magento', true, $url);
        $errors = $this->validator->validate($value);

        $this->assertCount(1, $errors);
        $this->assertInstanceOf(Length::class, $errors[0]->getConstraint());
    }

    private function createValidUrlOfLength(int $length): string
    {
        $baseUrl = 'http://foo.com/';
        $url = sprintf('%s%s', $baseUrl, str_repeat('a', $length - strlen($baseUrl)));

        if (strlen($url) !== $length) {
            throw new \LogicException(sprintf('The url should have %d characters but has %d instead.', $length, strlen($url)));
        }

        return $url;
    }
}
