<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\UserIntentFactory;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\ValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueUserIntentFactoryRegistrySpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        ValueUserIntentFactory $valueUserIntentFactory1,
        ValueUserIntentFactory $valueUserIntentFactory2,
        ValueUserIntentFactory $valueUserIntentFactory3,
    ) {
        $valueUserIntentFactory1->getSupportedAttributeTypes()->willReturn(['pim_catalog_text']);
        $valueUserIntentFactory2->getSupportedAttributeTypes()->willReturn(['pim_catalog_identifier']);
        $valueUserIntentFactory3->getSupportedAttributeTypes()->willReturn(['pim_catalog_textarea']);

        $this->beConstructedWith($attributeRepository, [$valueUserIntentFactory1, $valueUserIntentFactory2, $valueUserIntentFactory3]);
        $this->shouldImplement(UserIntentFactory::class);
    }

    function it_returns_user_intents(
        AttributeRepositoryInterface $attributeRepository,
        ValueUserIntentFactory $valueUserIntentFactory1,
        ValueUserIntentFactory $valueUserIntentFactory2,
        ValueUserIntentFactory $valueUserIntentFactory3,
        ValueUserIntent $valueUserIntent1,
        ValueUserIntent $valueUserIntent2,
        ValueUserIntent $valueUserIntent3,
    ) {
        $valueUserIntentFactory1->getSupportedAttributeTypes()->willReturn(['pim_catalog_text']);
        $valueUserIntentFactory2->getSupportedAttributeTypes()->willReturn(['pim_catalog_identifier']);
        $valueUserIntentFactory3->getSupportedAttributeTypes()->willReturn(['pim_catalog_textarea']);

        $attributeRepository->getAttributeTypeByCodes(['a_text', 'sku', 'A_TExtAreA'])
            ->shouldBeCalledOnce()
            ->willReturn([
                'a_text' => 'pim_catalog_text',
                'sku' => 'pim_catalog_identifier',
                'A_TExtAreA' => 'pim_catalog_textarea',
            ]);

        $valueUserIntentFactory1->create('pim_catalog_text', 'a_text', ['data' => 'bonjour', 'locale' => null, 'scope' => null])
            ->shouldBeCalledOnce()
            ->willReturn($valueUserIntent1);
        $valueUserIntentFactory2->create('pim_catalog_identifier', 'sku', ['data' => 'my_sku'])
            ->shouldBeCalledOnce()
            ->willReturn($valueUserIntent2);
        $valueUserIntentFactory3->create('pim_catalog_textarea', 'A_TExtAreA', ['data' => '<p>bonjour</p>', 'locale' => null, 'scope' => null])
            ->shouldBeCalledOnce()
            ->willReturn($valueUserIntent3);

        $this->create('values', [
            'a_text' => [['data' => 'bonjour', 'locale' => null, 'scope' => null]],
            'sku' => [['data' => 'my_sku']],
            'A_TExtAreA' => [['data' => '<p>bonjour</p>', 'locale' => null, 'scope' => null]],
        ])->shouldReturn([$valueUserIntent1, $valueUserIntent2, $valueUserIntent3]);
    }
}
