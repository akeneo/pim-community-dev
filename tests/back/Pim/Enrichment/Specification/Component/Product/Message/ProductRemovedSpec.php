<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Message;

use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Platform\Component\EventQueue\Event;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductRemovedSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            [
                'identifier' => 'product_identifier',
                'uuid' => Uuid::fromString('5dd9eb8b-261f-4e76-bf1d-f407063f931d'),
                'category_codes' => ['category_code_1', 'category_code_2'],
            ],
            1598968800,
            '523e4557-e89b-12d3-a456-426614174000',
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProductRemoved::class);
    }

    public function it_is_an_event(): void
    {
        $this->shouldBeAnInstanceOf(Event::class);
    }

    public function it_validates_the_product_identifier(): void
    {
        $this->beConstructedWith(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            [],
            1598968800,
            '523e4557-e89b-12d3-a456-426614174000',
        );

        $this->shouldThrow(
            new \InvalidArgumentException('Expected the key "identifier" to exist.'),
        )->duringInstantiation();
    }

    public function it_validates_the_product_uuid(): void
    {
        $this->beConstructedWith(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            [
                'identifier' => 'product_identifier',
                'category_codes' => ['category_code_1', 'category_code_2'],
            ],
            1598968800,
            '523e4557-e89b-12d3-a456-426614174000',
        );

        $this->shouldThrow(
            new \InvalidArgumentException('Expected the key "uuid" to exist.'),
        )->duringInstantiation();
    }

    public function it_validates_the_category_codes(): void
    {
        $this->beConstructedWith(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            [
                'identifier' => 'product_identifier',
                'uuid' => Uuid::uuid4(),
            ],
            1598968800,
            '523e4557-e89b-12d3-a456-426614174000',
        );

        $this->shouldThrow(
            new \InvalidArgumentException('Expected the key "category_codes" to exist.'),
        )->duringInstantiation();
    }

    public function it_returns_the_name(): void
    {
        $this->getName()->shouldReturn('product.removed');
    }

    public function it_returns_the_author(): void
    {
        $this->getAuthor()->shouldBeLike(Author::fromNameAndType('julia', Author::TYPE_UI));
    }

    public function it_returns_the_data(): void
    {
        $this->getData()->shouldBeLike([
            'identifier' => 'product_identifier',
            'uuid' => Uuid::fromString('5dd9eb8b-261f-4e76-bf1d-f407063f931d'),
            'category_codes' => ['category_code_1', 'category_code_2'],
        ]);
    }

    public function it_returns_the_timestamp(): void
    {
        $this->getTimestamp()->shouldReturn(1598968800);
    }

    public function it_returns_the_uuid(): void
    {
        $this->getUuid()->shouldReturn('523e4557-e89b-12d3-a456-426614174000');
    }

    public function it_returns_the_product_uuid(): void
    {
        $this->getProductUuid()->shouldBeLike(Uuid::fromString('5dd9eb8b-261f-4e76-bf1d-f407063f931d'));
    }

    public function it_returns_the_product_identifier(): void
    {
        $this->getIdentifier()->shouldReturn('product_identifier');
    }

    public function it_returns_the_category_codes(): void
    {
        $this->getCategoryCodes()->shouldReturn(['category_code_1', 'category_code_2']);
    }
}
