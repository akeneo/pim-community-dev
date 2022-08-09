<?php
declare(strict_types=1);


/**
 * A TemplateReadModel represents the information returned by the GetTemplateQuery query.
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\Api\Model;

use Akeneo\Category\Api\Model\Template\AttributeDefinition;
use Akeneo\Category\Api\Model\Template\Code;
use Akeneo\Category\Api\Model\Template\LabelCollection;
use Akeneo\Category\Api\Model\Template\TemplateId;

class TemplateReadModel
{
    /**
     * @param AttributeDefinition[] $attributes
     */
public function __construct(
    private TemplateId $id,
    private Code $code,
    private LabelCollection $labels,
    private array $attributes

) {

}

}
