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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributeOptionsMappingCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributeOptionsMappingQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\ValueObject\AttributeOptions;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeId;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\OptionsMappingNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeOptionsMappingController
{
    /** @var GetAttributeOptionsMappingHandler */
    private $getAttributeOptionsMappingHandler;

    /** @var SaveAttributeOptionsMappingHandler */
    private $saveAttributeOptionsMappingHandler;

    /**
     * @param GetAttributeOptionsMappingHandler $getAttributeOptionsMappingHandler
     * @param SaveAttributeOptionsMappingHandler $saveAttributeOptionsMappingHandler
     */
    public function __construct(
        GetAttributeOptionsMappingHandler $getAttributeOptionsMappingHandler,
        SaveAttributeOptionsMappingHandler $saveAttributeOptionsMappingHandler
    ) {
        $this->getAttributeOptionsMappingHandler = $getAttributeOptionsMappingHandler;
        $this->saveAttributeOptionsMappingHandler = $saveAttributeOptionsMappingHandler;
    }

    /**
     * @param string $identifier
     * @param string $franklinAttributeCode
     *
     * @return JsonResponse
     */
    public function getAction(string $identifier, string $franklinAttributeCode): JsonResponse
    {
        $query = new GetAttributeOptionsMappingQuery(
            new FamilyCode($identifier),
            new FranklinAttributeId($franklinAttributeCode)
        );
        $attributeOptionsMapping = $this->getAttributeOptionsMappingHandler->handle($query);

        $normalizer = new OptionsMappingNormalizer();

        return new JsonResponse(
            $normalizer->normalize($attributeOptionsMapping)
        );
    }

    /**
     * @param Request $request
     *
     * TODO: handle the status for each option
     *
     * Current return from the Front :
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
     * }
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request): JsonResponse
    {
        $requestContent = json_decode($request->getContent(), true);

        $this->validateAttributeOptionsMapping($requestContent);

        try {
            $command = new SaveAttributeOptionsMappingCommand(
                new FamilyCode($requestContent['family']),
                new AttributeCode($requestContent['catalogAttributeCode']),
                new FranklinAttributeId($requestContent['franklinAttributeCode']),
                new AttributeOptions($requestContent['mapping'])
            );

            $this->saveAttributeOptionsMappingHandler->handle($command);

            return new JsonResponse();
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['errors' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param array $requestContent
     */
    private function validateAttributeOptionsMapping(array $requestContent): void
    {
        $expectedKeys = [
            'family',
            'catalogAttributeCode',
            'franklinAttributeCode',
            'mapping',
        ];

        foreach ($expectedKeys as $key) {
            if (!array_key_exists($key, $requestContent)) {
                throw new \InvalidArgumentException(sprintf(
                    'Missing "%s" key in save attribute options mapping request',
                    $key
                ));
            }
        }
    }
}
