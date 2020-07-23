<?php

declare(strict_types=1);

namespace spec\Akeneo\SharedCatalog\Model;

use Akeneo\SharedCatalog\Model\SharedCatalog;
use PhpSpec\ObjectBehavior;

class SharedCatalogSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            'shared_catalog',
            null,
            [],
            null,
            null
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SharedCatalog::class);
    }

    public function it_is_initializable_with_values()
    {
        $this->beConstructedWith(
            'shared_catalog',
            'julia',
            [
                [
                    'email' => 'betty@akeneo.com',
                ],
            ],
            [
                'structure' => [
                    'scope' => 'mobile',
                    'locales' => [
                        'en_US',
                    ],
                    'attributes' => [
                        'name',
                    ],
                ],
            ],
            [
                'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABKoAAAJFCAYAAAD9Ih9',
            ]
        );

        $this->shouldHaveType(SharedCatalog::class);
    }

    public function it_can_be_normalized_for_external_api()
    {
        $this->beConstructedWith(
            'shared_catalog',
            'julia',
            [
                [
                    'email' => 'betty@akeneo.com',
                ],
            ],
            [
                'structure' => [
                    'scope' => 'mobile',
                    'locales' => [
                        'en_US',
                    ],
                    'attributes' => [
                        'name',
                    ],
                ],
            ],
            [
                'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABKoAAAJFCAYAAAD9Ih9',
            ]
        );

        $this->normalizeForExternalApi()->shouldBeLike([
            'code' => 'shared_catalog',
            'publisher' => 'julia',
            'recipients' => [
                'betty@akeneo.com',
            ],
            'channel' => 'mobile',
            'catalogLocales' => [
                'en_US',
            ],
            'attributes' => [
                'name',
            ],
            'branding' => [
                'logo' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABKoAAAJFCAYAAAD9Ih9',
            ],
        ]);
    }

    public function it_can_return_the_default_scope()
    {
        $this->beConstructedWith(
            'shared_catalog',
            'julia',
            [],
            [
                'structure' => [
                    'scope' => 'mobile',
                ],
            ],
            []
        );

        $this->getDefaultScope()->shouldEqual('mobile');
    }

    public function it_returns_null_if_there_is_no_default_scope()
    {
        $this->beConstructedWith(
            'shared_catalog',
            'julia',
            [],
            [],
            []
        );

        $this->getDefaultScope()->shouldBeNull();
    }

    public function it_can_return_the_pqb_filters()
    {
        $this->beConstructedWith(
            'shared_catalog',
            'julia',
            [],
            [
                'data' => [
                    [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => true,
                    ],
                ],
            ],
            []
        );

        $this->getPQBFilters()->shouldEqual([
            [
                'field' => 'enabled',
                'operator' => '=',
                'value' => true,
            ],
        ]);
    }

    public function it_returns_an_empty_array_if_there_is_no_pqb_filters()
    {
        $this->beConstructedWith(
            'shared_catalog',
            'julia',
            [],
            [],
            []
        );

        $this->getPQBFilters()->shouldEqual([]);
    }
}
