<?php

declare(strict_types=1);

namespace Akeneo\Category\Api\Query;


/**
 * Getting information about a category template
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetTemplateQuery
{
    /**
     * @param TemplateId $id the id of the category to get
     */
    public function __construct(
        private TemplateId $id,
    ) {
    }

    public function templateId(): TemplateId
    {
        return $this->id;
    }

}
