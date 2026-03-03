<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class InternalApiTestCase extends TestCase
{
    /** @var HttpKernelBrowser */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->getClient();
    }

    protected function authenticate(UserInterface $user): void
    {
        $firewallName = 'main';
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($user, $firewallName, $user->getRoles());
        $session = $this->getSession();
        $session->set('_security_' . $firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    protected function createProduct(string $identifier, ?string $familyCode, array $userIntents): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $familyCode ? [
                new SetFamily($familyCode),
                ...$userIntents
            ] : $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->clearDoctrineUoW();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    protected function createProductModel(array $data = []) : ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($productModel);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf(
                'Impossible to setup test in %s: %s',
                static::class,
                $errors->get(0)->getMessage()
            ));
        }
        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $productModel;
    }

    private function getClient(): HttpKernelBrowser
    {
        return self::getContainer()->get('test.client');
    }

    private function getSession(): SessionInterface
    {
        return self::getContainer()->get('session');
    }

    protected function clearDoctrineUoW(): void
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }
}
