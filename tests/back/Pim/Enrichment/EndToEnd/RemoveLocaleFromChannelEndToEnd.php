<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetProductCompletenessRatio;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RemoveLocaleFromChannelEndToEnd extends InternalApiTestCase
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var Client */
    private $esProductClient;

    /** @var IdentifiableObjectRepositoryInterface */
    private $channelRepository;

    /** @var GetProductCompletenessRatio */
    private $getCompletenessRatio;

    public function setUp(): void
    {
        parent::setUp();

        $this->authenticate($this->createAdminUser());
        $this->validator = $this->get('validator');
        $this->channelRepository = $this->get('pim_catalog.repository.channel');
        $this->esProductClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        $this->getCompletenessRatio = $this->get('akeneo.pim.enrichment.product.query.product_completeness_ratio');
    }

    public function test_that_removing_a_locale_from_a_channel_recomputes_the_products_completeness(): void
    {
        $this->createFixtures();

        $url = $this->getRouter()->generate('pim_enrich_channel_rest_put', ['code' => 'ecommerce']);
        $this->client->request(
            'PUT',
            $url,
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ],
            json_encode([
                'code' => 'ecommerce',
                'locales' => ['en_US'],
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->waitForJobExecutionToEnd();

        $blueJean = $this->getProductId('blue_jean');
        $blueJeanRatioEn = $this->getCompletenessRatio->forChannelCodeAndLocaleCode($blueJean, 'ecommerce', 'en_US');
        $blueJeanRatioFr = $this->getCompletenessRatio->forChannelCodeAndLocaleCode($blueJean, 'ecommerce', 'fr_FR');
        $this->assertEquals(100, $blueJeanRatioEn);
        $this->assertNull($blueJeanRatioFr);

        $yellowJean = $this->getProductId('yellow_jean');
        $yellowJeanRatioEn = $this->getCompletenessRatio->forChannelCodeAndLocaleCode($yellowJean, 'ecommerce', 'en_US');
        $yellowJeanRatioFr = $this->getCompletenessRatio->forChannelCodeAndLocaleCode($yellowJean, 'ecommerce', 'fr_FR');
        $this->assertEquals(50, $yellowJeanRatioEn);
        $this->assertNull($yellowJeanRatioFr);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * An attribute: 'a_scopable_localizable_text'
     *
     * A family: 'jeans'
     *      with requirement on 'ecommerce' channel 'a_scopable_localizable_text'
     *
     * 2 products:
     *      - 'blue_jean'
     *          - 'en_US' 100% complete
     *          - 'fr_FR' 100% complete
     *      - 'yellow_jean'
     *          - 'en_US' 50% complete
     *          - 'fr_FR' 100% complete
     */
    private function createFixtures(): void
    {
        $ecommerceChannel = $this->updateEcommerceChannelWithFRLocale();
        $attribute = $this->createAttribute([
            'code' => 'a_scopable_localizable_text',
            'type' => AttributeTypes::TEXT,
            'localizable' => true,
            'scopable' => true,
        ]);
        $this->createFamilyWithRequirement('jeans', $attribute, $ecommerceChannel);

        $this->createProduct('blue_jean', [
            'family' => 'jeans',
            'values' => [
                'a_scopable_localizable_text' => [
                    ['data' => 'blue', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'bleu', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                ]
            ]
        ]);

        $this->createProduct('yellow_jean', [
            'family' => 'jeans',
            'values' => [
                'a_scopable_localizable_text' => [
                    ['data' => 'yellow_jean', 'locale' => 'fr_FR', 'scope' => 'ecommerce']
                ]
            ]
        ]);
    }

    private function getRouter(): RouterInterface
    {
        return self::$container->get('router');
    }

    private function waitForJobExecutionToEnd()
    {
        $launcher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        while ($launcher->hasJobInQueue()) {
            $launcher->launchConsumerOnce();
        }
        $maxRetry = 30;
        for ($retry = 0; $retry < $maxRetry; $retry++) {
            sleep(1);
            if ($this->areJobExecutionsEnded()) {
                return;
            }
        }
        throw new \Exception('Job "remove_completeness_for_channel_and_locale" not finished.');
    }

    private function areJobExecutionsEnded(): bool
    {
        $jobExecutionsEnded = true;

        $jobInstance = $this->get('pim_enrich.repository.job_instance')->findOneBy([
            'code' => 'remove_completeness_for_channel_and_locale'
        ]);
        $jobExecutions = $jobInstance->getJobExecutions();

        foreach ($jobExecutions as $jobExecution) {
            $jobExecutionStatus = $jobExecution->getStatus();
            if ($jobExecutionStatus->isRunning() || $jobExecutionStatus->isStarting()) {
                $jobExecutionsEnded = false;
            }
        }

        return $jobExecutionsEnded;
    }

    private function updateEcommerceChannelWithFRLocale(): ChannelInterface
    {
        $ecommerceChannel = $this->channelRepository->findOneByIdentifier('ecommerce');
        $this->get('pim_catalog.updater.channel')->update($ecommerceChannel, ['locales' => ['en_US', 'fr_FR']]);
        $this->validate($ecommerceChannel);
        $this->get('pim_catalog.saver.channel')->save($ecommerceChannel);

        return $ecommerceChannel;
    }

    private function createProduct($identifier, array $data): void
    {
        $family = isset($data['family']) ? $data['family'] : null;

        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $family);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->validate($product);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->esProductClient->refreshIndex();
    }

    private function validate($entity): void
    {
        $violations = $this->validator->validate($entity);
        $this->assertCount(0, $violations);
    }

    private function createAttribute(array $data): AttributeInterface
    {
        $data['group'] = $data['group'] ?? 'other';

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $this->validate($attribute);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    private function createFamilyWithRequirement(
        string $familyCode,
        AttributeInterface $attribute,
        ChannelInterface $channel
    ): void {
        $requirement = $this->get('pim_catalog.factory.attribute_requirement')
            ->createAttributeRequirement($attribute, $channel, true);

        $family = $this->get('pim_catalog.factory.family')->create();
        $family->setCode($familyCode);
        $family->addAttribute($attribute);
        $family->addAttributeRequirement($requirement);
        $this->validate($family);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function getProductId(string $productIdentifier): int
    {
        /** @var IdentifiableObjectRepositoryInterface $productRepository */
        $productRepository = $this->get('pim_catalog.repository.product');
        /** @var ProductInterface $product */
        $product = $productRepository->findOneByIdentifier($productIdentifier);

        return (int) $product->getId();
    }
}
