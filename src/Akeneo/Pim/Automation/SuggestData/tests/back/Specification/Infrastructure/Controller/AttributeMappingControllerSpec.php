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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateAttributesMappingByFamilyCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Normalizer\InternalApi\AttributesMappingNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class AttributeMappingControllerSpec extends ObjectBehavior
{
    function let(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        AttributesMappingNormalizer $attributesMappingNormalizer,
        UpdateAttributesMappingByFamilyHandler $updateAttributesMappingByFamilyHandler
    ) {
        $this->beConstructedWith(
            $getAttributesMappingByFamilyHandler,
            $attributesMappingNormalizer,
            $updateAttributesMappingByFamilyHandler
        );
    }

    function it_updates_attributes_mapping(
        $updateAttributesMappingByFamilyHandler,
        Request $request
    ) {
        $jsonContent = $this->loadAttributesMappingRequestContent();
        $request->getContent()->willReturn($jsonContent);

        $command = new UpdateAttributesMappingByFamilyCommand('camcorders');
        $updateAttributesMappingByFamilyHandler->handle($command)->shouldBeCalled();

        $this->update($request)->shouldReturnAnInstanceOf(JsonResponse::class);
    }

    /**
     * Loads mocked data for front-end
     *
     * @return bool|string
     */
    private function loadAttributesMappingRequestContent()
    {
        $filepath =
            sprintf(
                '%s/%s',
                realpath(sprintf('%s/%s', __DIR__, '../../../resources/mapping/')),
                'akeneo_front_attributes_mapping.json'
            );

        return file_get_contents($filepath);
    }
}
