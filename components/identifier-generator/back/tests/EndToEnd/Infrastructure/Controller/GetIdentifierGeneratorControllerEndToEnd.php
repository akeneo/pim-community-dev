<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Saver\BaseSaver;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetIdentifierGeneratorControllerEndToEnd extends ControllerEndToEndTestCase
{
    /** @test */
    public function it_should_redirect_on_non_xhr_request(): void
    {
        $this->loginAs('Julia');
        $this->callGetRoute('akeneo_identifier_generator_rest_get', 'code', [
            'HTTP_X-Requested-With' => 'toto',
        ]);
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FOUND, $response->getStatusCode());
        Assert::assertTrue($response->isRedirect('/'));
    }

    /** @test */
    public function it_should_return_http_forbidden_without_the_view_generators_acl(): void
    {
        $this->loginAs('kevin');
        $this->callGetRoute('akeneo_identifier_generator_rest_get', 'code');
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /** @test */
    public function it_throws_an_error_if_code_does_not_exist(): void
    {
        $this->loginAs('Julia');
        $this->callGetRoute('akeneo_identifier_generator_rest_get', 'unknown');
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        Assert::assertSame('"Identifier generator \u0022unknown\u0022 does not exist or you do not have permission to access it."', $response->getContent());
    }

    /** @test */
    public function it_returns_an_identifier_generator(): void
    {
        $this->createSimpleSelectAttributeWithOptions('simple_select_localizable_scopable', ['optionA', 'optionB'], true, true);
        $identifierGeneratorData = [
            'code' => 'my_new_generator',
            'labels' => [
                'en_US' => 'My new generator',
                'fr_FR' => 'Mon nouveau générateur',
            ],
            'target' => 'sku',
            'conditions' => [],
            'structure' => [
                [
                    'type' => 'free_text',
                    'string' => 'AKN',
                ],
                [
                    'type' => 'simple_select',
                    'attributeCode' => 'a_simple_select',
                    'process' => ['type' => 'no'],
                ],
                [
                    'type' => 'simple_select',
                    'attributeCode' => 'simple_select_localizable_scopable',
                    'process' => ['type' => 'no'],
                    'locale' => 'en_US',
                    'scope' => 'ecommerce',
                ],
            ],
            'delimiter' => null,
            'text_transformation' => 'no',
        ];

        $this->loginAs('Julia');
        $this->callCreateRoute(
            'akeneo_identifier_generator_rest_create',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode($identifierGeneratorData),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $this->callGetRoute('akeneo_identifier_generator_rest_get', 'my_new_generator');
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $uuid = $this->getUuidFromCode('my_new_generator');
        Assert::assertSame(
            \sprintf(
                '{"uuid":"%s","code":"my_new_generator","conditions":[],"structure":[{"type":"free_text","string":"AKN"},{"type":"simple_select","attributeCode":"a_simple_select","process":{"type":"no"}},{"type":"simple_select","attributeCode":"simple_select_localizable_scopable","process":{"type":"no"},"scope":"ecommerce","locale":"en_US"}],"labels":{"en_US":"My new generator","fr_FR":"Mon nouveau g\u00e9n\u00e9rateur"},"target":"sku","delimiter":null,"text_transformation":"no"}',
                $uuid
            ),
            $response->getContent()
        );
    }

    private function getUuidFromCode(string $code): string
    {
        return $this->get('database_connection')->executeQuery(<<<SQL
SELECT BIN_TO_UUID(uuid) AS uuid FROM pim_catalog_identifier_generator WHERE code=:code
SQL, ['code' => $code])->fetchOne();
    }

    private function createSimpleSelectAttributeWithOptions(string $code, array $optionCodes, bool $localizable, bool $scopable): AttributeInterface
    {
        $attribute = $this->createAttribute([
            'code' => $code,
            'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
            'group' => 'other',
            'localizable' => $localizable,
            'scopable' => $scopable,
        ]);

        foreach ($optionCodes as $sortOrder => $optionCode) {
            $option = $this->getAttributeOptionFactory()->create();
            $option->setCode($optionCode);
            $option->setAttribute($attribute);
            $option->setSortOrder($sortOrder);
            $this->getAttributeOptionSaver()->save($option);
        }

        return $attribute;
    }

    private function getAttributeOptionFactory(): SimpleFactoryInterface
    {
        return $this->get('pim_catalog.factory.attribute_option');
    }

    private function getAttributeOptionSaver(): BaseSaver
    {
        return $this->get('pim_catalog.saver.attribute_option');
    }
}
