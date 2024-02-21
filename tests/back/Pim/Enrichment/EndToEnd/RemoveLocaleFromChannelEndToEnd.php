<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetProductCompletenessRatio;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RemoveLocaleFromChannelEndToEnd extends InternalApiTestCase
{
    private ValidatorInterface $validator;
    private IdentifiableObjectRepositoryInterface $channelRepository;
    private GetProductCompletenessRatio $getCompletenessRatio;
    private LocaleRepositoryInterface $localeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->authenticate($this->createAdminUser());
        $this->validator = $this->get('validator');
        $this->channelRepository = $this->get('pim_catalog.repository.channel');
        $this->getCompletenessRatio = $this->get('akeneo.pim.enrichment.product.query.product_completeness_ratio');
        $this->localeRepository = $this->get('pim_catalog.repository.locale');
    }

    public function testThatRemovingALocaleFromAChannelRecomputesTheProductsCompleteness(): void
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

        $blueJeanUuid = $this->getProductUuid('blue_jean');
        $blueJeanRatioEn = $this->getCompletenessRatio->forChannelCodeAndLocaleCode($blueJeanUuid, 'ecommerce', 'en_US');
        $blueJeanRatioFr = $this->getCompletenessRatio->forChannelCodeAndLocaleCode($blueJeanUuid, 'ecommerce', 'fr_FR');
        $this->assertEquals(100, $blueJeanRatioEn);
        $this->assertNull($blueJeanRatioFr);

        $yellowJeanUuid = $this->getProductUuid('yellow_jean');
        $yellowJeanRatioEn = $this->getCompletenessRatio->forChannelCodeAndLocaleCode($yellowJeanUuid, 'ecommerce', 'en_US');
        $yellowJeanRatioFr = $this->getCompletenessRatio->forChannelCodeAndLocaleCode($yellowJeanUuid, 'ecommerce', 'fr_FR');
        $this->assertEquals(50, $yellowJeanRatioEn);
        $this->assertNull($yellowJeanRatioFr);
    }

    public function testItDeactivateLocaleWhenRemovingOnlyChannelWithIt(): void
    {
        $this->createFixtures();
        $this->assertEquals($this->isActivatedLocale('br_FR'), false);

        $newUrl = $this->getRouter()->generate('pim_enrich_channel_rest_post', ['code' => 'my_channel']);
        $this->client->request(
            'POST',
            $newUrl,
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ],
            json_encode([
                'code' => 'my_channel',
                'category_tree' => 'master',
                'currencies' => ['EUR', 'USD'],
                'locales' => ['br_FR']
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->waitForJobExecutionToEnd();

        $this->assertEquals($this->isActivatedLocale('br_FR'), true);

        $deleteUrl = $this->getRouter()->generate('pim_enrich_channel_rest_remove', ['code' => 'my_channel']);
        $this->client->request(
            'DELETE',
            $deleteUrl,
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ],
            json_encode([
                'code' => 'my_channel',
            ])
        );
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->waitForJobExecutionToEnd();

        $this->assertEquals($this->isActivatedLocale('br_FR'), false, 'locale must be deactivated');
    }

    public function testItDoesntCreateChannelOrLocaleIfChannelIsInvalid(): void
    {
        $this->createFixtures();
        $this->assertEquals($this->isActivatedLocale('br_FR'), false);

        $newUrl = $this->getRouter()->generate('pim_enrich_channel_rest_post', ['code' => 'my channel with space']);
        $this->client->request(
            'POST',
            $newUrl,
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ],
            json_encode([
                'code' => 'my channel with space',
                'category_tree' => 'master',
                'currencies' => ['EUR', 'USD'],
                'locales' => ['br_FR']
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $expectedContent =
            <<<JSON
{"code":{"message":"Channel code may contain only letters, numbers and underscores and should contain at least one letter"}}
JSON;
        $this->assertEquals($expectedContent, $response->getContent());
        $this->waitForJobExecutionToEnd();

        $this->assertEquals($this->isActivatedLocale('br_FR'), false);
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

        $this->createProduct('blue_jean', 'jeans', [
            new SetTextValue('a_scopable_localizable_text', 'ecommerce', 'en_US', 'blue'),
            new SetTextValue('a_scopable_localizable_text', 'ecommerce', 'fr_FR', 'bleu'),
        ]);

        $this->createProduct('yellow_jean', 'jeans', [
            new SetTextValue('a_scopable_localizable_text', 'ecommerce', 'fr_FR', 'yellow_jean')
        ]);
    }

    private function getRouter(): RouterInterface
    {
        return self::getContainer()->get('router');
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

    private function isActivatedLocale(string $localeCode): bool
    {
        $activatedLocales = $this->localeRepository->getActivatedLocaleCodes();

        return \in_array($localeCode, $activatedLocales);
    }
}
