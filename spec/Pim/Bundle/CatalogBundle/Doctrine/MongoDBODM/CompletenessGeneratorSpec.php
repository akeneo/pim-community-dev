<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\PersistentCollection;

use PhpSpec\ObjectBehavior;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class CompletenessGeneratorSpec extends ObjectBehavior
{
    function let(
        DocumentManager $manager,
        ChannelManager $channelManager,
        FamilyRepository $familyRepository
    ) {
        $this->beConstructedWith($manager, 'pim_product_class', $channelManager, $familyRepository);
    }

    function it_is_a_completeness_generator()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface');
    }

    function it_schedules_product_completeness(
        $manager,
        ProductInterface $product,
        PersistentCollection $completenesses
    ) {
        $manager->flush($product)->shouldNotBeCalled();
        $product->getCompletenesses()->willReturn($completenesses);
        $completenesses->clear()->shouldBeCalled();

        $this->schedule($product);
    }

    function it_generates_all_product_completenesses()
    {
        $normalizedReqs = [
            'ecommerce-en_US' => [
                'channel' => 1,
                'locale'  => 58,
                'reqs' => [
                    'attributes' => [
                        'sku','name','description'
                    ],
                    'prices' => [
                    ]
                ]
            ]
        ];
        $normalizedData = [
            'sku' => 'sku001',
            'name' => 'My product',
        ];
        $expectedResult = [
            'completenesses' => [
                'ecommerce-en_US' => [
                    'object' => [
                        'missingCount' => 1,
                        'requiredCount' => 3,
                        'ratio' => (float) 67,
                        'channel' => 1,
                        'locale' => 58
                    ],
                    'ratio' => (float) 67
                ]
            ],
            'all' => true
        ];

        $this->getCompletenesses($normalizedData, $normalizedReqs)->shouldReturn($expectedResult);
    }

    function it_generates_all_multiple_product_completenesses()
    {
        $normalizedReqs = [
            'ecommerce-en_US' => [
                'channel' => 1,
                'locale'  => 58,
                'reqs' => [
                    'attributes' => [
                        'sku','name','description'
                    ],
                    'prices' => [
                    ]
                ]
            ],
            'mobile-en_US' => [
                'channel' => 2,
                'locale'  => 58,
                'reqs' => [
                    'attributes' => [
                        'sku','description'
                    ],
                    'prices' => [
                    ]
                ]
            ],
            'mobile-fr_FR' => [
                'channel' => 2,
                'locale'  => 62,
                'reqs' => [
                    'attributes' => [
                        'sku'
                    ],
                    'prices' => [
                    ]
                ]
            ]
        ];
        $normalizedData = [
            'sku' => 'sku001',
            'name' => 'My product'
        ];
        $expectedResult = [
            'completenesses' => [
                'ecommerce-en_US' => [
                    'object' => [
                        'missingCount' => 1,
                        'requiredCount' => 3,
                        'ratio' => (float) 67,
                        'channel' => 1,
                        'locale' => 58
                    ],
                    'ratio' => (float) 67
                ],
                'mobile-en_US' => [
                    'object' => [
                        'missingCount' => 1,
                        'requiredCount' => 2,
                        'ratio' => (float) 50,
                        'channel' => 2,
                        'locale' => 58
                    ],
                    'ratio' => (float) 50
                ],
                'mobile-fr_FR' => [
                    'object' => [
                        'missingCount' => 0,
                        'requiredCount' => 1,
                        'ratio' => (float) 100,
                        'channel' => 2,
                        'locale' => 62
                    ],
                    'ratio' => (float) 100
                ]
            ],
            'all' => true
        ];

        $this->getCompletenesses($normalizedData, $normalizedReqs)->shouldReturn($expectedResult);
    }

    function it_generates_missing_completenesses_only()
    {
        $normalizedReqs = [
            'ecommerce-en_US' => [
                'channel' => 1,
                'locale'  => 58,
                'reqs' => [
                    'attributes' => [
                        'sku','name','description'
                    ],
                    'prices' => [
                    ]
                ]
            ],
            'mobile-en_US' => [
                'channel' => 2,
                'locale'  => 58,
                'reqs' => [
                    'attributes' => [
                        'sku','description'
                    ],
                    'prices' => [
                    ]
                ]
            ],
            'mobile-fr_FR' => [
                'channel' => 2,
                'locale'  => 62,
                'reqs' => [
                    'attributes' => [
                        'sku'
                    ],
                    'prices' => [
                    ]
                ]
            ]
        ];

        $normalizedData = [
            'sku' => 'sku001',
            'name' => 'My product',
            'completenesses' => [
                'ecommerce-en_US' => 50,
                'mobile-fr_FR' => 50,
            ]
        ];

        $expectedResult = [
            'completenesses' => [
                'mobile-en_US' => [
                    'object' => [
                        'missingCount' => 1,
                        'requiredCount' => 2,
                        'ratio' => (float) 50,
                        'channel' => 2,
                        'locale' => 58
                    ],
                    'ratio' => (float) 50
                ]
            ],
            'all' => false
        ];

        $this->getCompletenesses($normalizedData, $normalizedReqs)->shouldReturn($expectedResult);
    }

    function it_generates_completenesse_with_prices()
    {
        $normalizedReqs = [
            'mobile-fr_FR' => [
                'channel' => 2,
                'locale'  => 62,
                'reqs' => [
                    'attributes' => [
                        'sku'
                    ],
                    'prices' => [
                        'price-fr_FR' => [
                            'EUR','USD'
                        ]
                    ]
                ]
            ]
        ];

        $normalizedData = [
            'sku' => 'sku001',
            'name' => 'My product',
            'price-fr_FR' => [
                'EUR' => [
                    'data' => 13.24,
                    'currency' => 'EUR'
                ],
                'USD' => [
                    'data' => 15.67,
                    'currency' => 'USD'
                ]
            ]
        ];

        $expectedResult = [
            'completenesses' => [
                'mobile-fr_FR' => [
                    'object' => [
                        'missingCount' => 0,
                        'requiredCount' => 2,
                        'ratio' => (float) 100,
                        'channel' => 2,
                        'locale' => 62
                    ],
                    'ratio' => (float) 100
                ]
            ],
            'all' => true
        ];

        $this->getCompletenesses($normalizedData, $normalizedReqs)->shouldReturn($expectedResult);
    }

    function it_generates_completenesse_with_incomplete_prices()
    {
        $normalizedReqs = [
            'mobile-fr_FR' => [
                'channel' => 2,
                'locale'  => 62,
                'reqs' => [
                    'attributes' => [
                        'sku'
                    ],
                    'prices' => [
                        'price-fr_FR' => [
                            'EUR','USD'
                        ]
                    ]
                ]
            ]
        ];

        $normalizedData = [
            'sku' => 'sku001',
            'name' => 'My product',
            'price-fr_FR' => [
                'EUR' => [
                    'data' => 13.24,
                    'currency' => 'EUR'
                ]
            ]
        ];

        $expectedResult = [
            'completenesses' => [
                'mobile-fr_FR' => [
                    'object' => [
                        'missingCount' => 1,
                        'requiredCount' => 2,
                        'ratio' => (float) 50,
                        'channel' => 2,
                        'locale' => 62
                    ],
                    'ratio' => (float) 50
                ]
            ],
            'all' => true
        ];

        $this->getCompletenesses($normalizedData, $normalizedReqs)->shouldReturn($expectedResult);
    }
}
