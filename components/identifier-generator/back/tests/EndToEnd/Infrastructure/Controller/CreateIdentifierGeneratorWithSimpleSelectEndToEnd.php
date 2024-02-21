<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater;
use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CreateIdentifierGeneratorWithSimpleSelectEndToEnd extends ControllerEndToEndTestCase
{
    private const VALID_IDENTIFIER = [
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
                'attributeCode' => 'a_simple_select_color',
                'process' => [
                    'type' => Process::PROCESS_TYPE_NO,
                ],
            ],
        ],
        'delimiter' => '-',
        'text_transformation' => 'no',
    ];

    /** @test */
    public function it_should_create_a_generator(): void
    {
        $this->loginAs('Julia');
        $this->callCreateRoute(
            'akeneo_identifier_generator_rest_create',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode(self::VALID_IDENTIFIER),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $uuid = $this->getUuidFromCode('my_new_generator');
        Assert::assertSame(
            \sprintf(
                '{"uuid":"%s","code":"my_new_generator","conditions":[],"structure":[{"type":"free_text","string":"AKN"},{"type":"simple_select","attributeCode":"a_simple_select_color","process":{"type":"no"}}],"labels":{"en_US":"My new generator","fr_FR":"Mon nouveau g\u00e9n\u00e9rateur"},"target":"sku","delimiter":"-","text_transformation":"no"}',
                $uuid
            ),
            $response->getContent()
        );
    }

    /** @test */
    public function it_should_create_a_generator_with_simple_select_scopable_and_localizable(): void
    {
        $this->loginAs('Julia');

        $this->createAttributeSimpleSelectScopableAndLocalizable('a_simple_select_color_scopable_and_localizable');

        $localizableSimpleSelectGenerator = self::VALID_IDENTIFIER;
        $localizableSimpleSelectGenerator['structure'][1] = [
            'type' => 'simple_select',
            'attributeCode' => 'a_simple_select_color_scopable_and_localizable',
            'process' => [
                'type' => Process::PROCESS_TYPE_NO,
            ],
            'locale' => 'en_US',
            'scope' => 'ecommerce',
        ];

        $this->callCreateRoute(
            'akeneo_identifier_generator_rest_create',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode($localizableSimpleSelectGenerator),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $uuid = $this->getUuidFromCode('my_new_generator');
        Assert::assertSame(
            \sprintf(
                '{"uuid":"%s","code":"my_new_generator","conditions":[],"structure":[{"type":"free_text","string":"AKN"},{"type":"simple_select","attributeCode":"a_simple_select_color_scopable_and_localizable","process":{"type":"no"},"scope":"ecommerce","locale":"en_US"}],"labels":{"en_US":"My new generator","fr_FR":"Mon nouveau g\u00e9n\u00e9rateur"},"target":"sku","delimiter":"-","text_transformation":"no"}',
                $uuid
            ),
            $response->getContent()
        );
    }

    private function createAttributeSimpleSelectScopableAndLocalizable(string $attributeCode): void
    {
        $attribute = $this->getAttributeFactory()->create();
        $this->getAttributeUpdater()->update($attribute, [
            'code' => $attributeCode,
            'type' => 'pim_catalog_simpleselect',
            'group' => 'other',
            'localizable' => true,
            'scopable' => true,
        ]);
        $attributeViolations = $this->getValidator()->validate($attribute);
        $this->assertCount(0, $attributeViolations);
        $this->getAttributeSaver()->save($attribute);
    }

    private function getUuidFromCode(string $code): string
    {
        return $this->get('database_connection')->executeQuery(<<<SQL
SELECT BIN_TO_UUID(uuid) AS uuid FROM pim_catalog_identifier_generator WHERE code=:code
SQL, ['code' => $code])->fetchOne();
    }

    private function getAttributeFactory(): AttributeFactory
    {
        return $this->get('pim_catalog.factory.attribute');
    }

    private function getAttributeUpdater(): AttributeUpdater
    {
        return $this->get('pim_catalog.updater.attribute');
    }

    private function getAttributeSaver(): AttributeSaver
    {
        return $this->get('pim_catalog.saver.attribute');
    }

    private function getValidator(): ValidatorInterface
    {
        return $this->get('validator');
    }
}
