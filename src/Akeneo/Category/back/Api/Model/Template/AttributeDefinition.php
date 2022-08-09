<?php
declare(strict_types=1);

namespace Akeneo\Category\Api\Model\Template;

use Akeneo\Category\Api\Model\Template\AttributeTypes;

    /**
 * This model represents a set of category attributes definitions in a category template as exposed to the outside of the category bounded context
 *
 * The order of the attribute in a template is external to this model, it is implicit when contained in an ordered collection
*
 * @phpstan-import-type AttributeType from AttributeTypes
 * @phpstan-type AttributeSettings array{"required": bool, "scopable":bool, "localizable": bool, "additional_properties": array<string, mixed>}
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class AttributeDefinition
{
    /**
     * @param AttributeType $type
     */
    public function __construct(
        private string $id,
        private string $code,
        private string $type,
        private LabelCollection $labels,
        private AttributeSettings $settings,
    ) {

    }

}
