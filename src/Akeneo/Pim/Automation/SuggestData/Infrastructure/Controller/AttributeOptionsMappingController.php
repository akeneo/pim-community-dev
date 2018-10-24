<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributeOptionsMappingQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FamilyCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FranklinAttributeId;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller\Normalizer\InternalApi\OptionsMappingNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeOptionsMappingController
{
    /** @var GetAttributeOptionsMappingHandler */
    private $getAttributeOptionsMappingHandler;

    /**
     * @param GetAttributeOptionsMappingHandler $getAttributeOptionsMappingHandler
     */
    public function __construct(
        GetAttributeOptionsMappingHandler $getAttributeOptionsMappingHandler
    ) {
        $this->getAttributeOptionsMappingHandler = $getAttributeOptionsMappingHandler;
    }

    /**
     * @return JsonResponse
     */
    public function getAction(string $familyCode, string $franklinAttributeId): JsonResponse
    {
        $query = new GetAttributeOptionsMappingQuery(
            new FamilyCode($familyCode),
            new FranklinAttributeId($franklinAttributeId)
        );
        $attributeOptionsMapping = $this->getAttributeOptionsMappingHandler->handle($query);

        $normalizer = new OptionsMappingNormalizer();

        return new JsonResponse(
            $normalizer->normalize($attributeOptionsMapping)
        );
    }

    /**
     * TODO Unmock data.
     *
     * @return JsonResponse
     */
    public function updateAction()
    {
        /* Current return from the Front
         * {
         *     "family":"router",
         *     "franklinAttributeCode":"color",
         *     "catalogAttributeCode": "color",
         *     "mapping":{
         *         "color_1":{
         *             "franklin_attribute_option_code":{"label":"Color 1"},
         *             "catalog_attribute_option_code":"color1",
         *             "status":0
         *         },
         *         "color_2":{
         *             "franklin_attribute_option_code":{"label":"Color 2"},
         *             "catalog_attribute_option_code":"color2",
         *             "status":1
         *         },
         *         "color_3":{
         *             "franklin_attribute_option_code":{"label":"Color 3"},
         *             "catalog_attribute_option_code":null,
         *             "status":2
         *         }
         *     }
         * }:
         */
        sleep(1);

        return new JsonResponse(['response' => 'It\'s a temporary OK!']);
    }
}
