<?php

declare(strict_types=1);

namespace spec\Akeneo\SharedCatalog\Model;

use Akeneo\SharedCatalog\Model\SharedCatalog;
use PhpSpec\ObjectBehavior;

class SharedCatalogSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(SharedCatalog::class);
    }

    public function it_is_initializable_with_values()
    {
        $this->beConstructedThrough('create', [
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
            ],
        ]);

        $this->shouldHaveType(SharedCatalog::class);
    }

    public function it_is_initializable_from_normalized()
    {
        $this->beConstructedThrough('createFromNormalized', [
            [
                'code' => 'shared_catalog',
                'publisher' => 'julia',
                'recipients' => [
                    [
                        'email' => 'betty@akeneo.com',
                    ],
                ],
                'filters' => [
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
                'branding' => [
                    'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABKoAAAJFCAYAAAD9Ih9',
                ],
            ],
        ]);

        $this->shouldHaveType(SharedCatalog::class);
    }

    public function it_can_be_normalized()
    {
        $normalized = [
            'code' => 'shared_catalog',
            'publisher' => 'julia',
            'recipients' => [
                [
                    'email' => 'betty@akeneo.com',
                ],
            ],
            'filters' => [
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
            'branding' => [
                'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABKoAAAJFCAYAAAD9Ih9',
            ],
        ];

        $this->beConstructedThrough('createFromNormalized', [
            $normalized,
        ]);

        $this->normalize()->shouldBeLike($normalized);
    }

    public function it_can_be_normalized_for_external_api()
    {
        $this->beConstructedThrough('createFromNormalized', [
            [
                'code' => 'shared_catalog',
                'publisher' => 'julia',
                'recipients' => [
                    [
                        'email' => 'betty@akeneo.com',
                    ],
                ],
                'filters' => [
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
                'branding' => [
                    'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABKoAAAJFCAYAAAD9Ih9',
                ],
            ],
        ]);

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
}
