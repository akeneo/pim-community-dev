<?php

declare(strict_types=1);

namespace Akeneo\Category\Application;

use Akeneo\Category\Api\Model\AttributeValues;
use Akeneo\Category\Api\Model\Permissions;
use Akeneo\Category\Api\Model\Template\AttributeDefinition;
use Akeneo\Category\Api\Model\Template\AttributeTypes;
use Akeneo\Category\Api\Model\Template\Code;
use Akeneo\Category\Api\Model\Template\LabelCollection;
use Akeneo\Category\Api\Model\Template\TemplateId;
use Akeneo\Category\Api\Model\TemplateReadModel;
use Akeneo\Category\Api\Query\GetTemplateQuery;

/**
 * a GetTemplatesHandler executes GetTemplateQuery queries
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetTemplateHandler
{
    public function __invoke(GetTemplateQuery $query): TemplateReadModel
    {

        $template = new TemplateReadModel(
            new TemplateId("de7c91f6-17fc-11ed-a629-879db27d1e5f"),
            new Code("My Template"),
            LabelCollection::fromArray([
                "fr_FR" => "Mon beau template",
                "en_US" => "My nice template",
                "de_DE" => "Mein schon templat"
            ]),
            [
                new AttributeDefinition(
                    '7012b776-17fd-11ed-a36e-a3e542759084',
                    "description",
                    AttributeTypes::TEXT,
                    LabelCollection::fromArray([
                        "fr_FR" => "Description",
                        "en_US" => "Description",
                    ]),
                    [
                        "required" => false,
                        "localizable" => true,
                        "scopable" => false,
                        "additional_properties" => []
                    ]
                )
            ]
        );

        return $template;
    }

}
