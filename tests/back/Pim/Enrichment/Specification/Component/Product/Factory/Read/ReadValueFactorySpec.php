<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Read;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\ReadValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Model\Attribute as WriteAttribute;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReadValueFactorySpec extends ObjectBehavior
{
    public function let(ReadValueFactory $factory1, ReadValueFactory $factory2, ValueFactory $writeValueFactory, IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $factory1->supportedAttributeType()->willReturn('an_attribute_type1');
        $factory2->supportedAttributeType()->willReturn('an_attribute_type2');
        $this->beConstructedWith([$factory1, $factory2], $writeValueFactory, $attributeRepository);
    }

    public function it_calls_the_right_factory(ReadValueFactory $factory2, ValueInterface $value)
    {
        $attribute = new Attribute('an_attribute', 'an_attribute_type2', [], false, false, null, false);
        $factory2->createWithoutCheckingData($attribute, null, null, 'data')->willReturn($value);
        $this->create($attribute, null, null, 'data')->shouldReturn($value);
    }

    public function it_fallbacks_to_the_write_value_factory_if_no_read_value_factory_has_been_found(
        ValueFactory $writeValueFactory,
        ValueInterface $value,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $writeAttribute = new WriteAttribute();
        $attributeRepository->findOneByIdentifier('an_attribute')->willReturn($writeAttribute);
        $attribute = new Attribute('an_attribute', 'an_attribute_type3', [], false, false, null, false);
        $writeValueFactory->create($writeAttribute, null, null, 'data')->willReturn($value);
        $this->create($attribute, null, null, 'data')->shouldReturn($value);
    }
}
