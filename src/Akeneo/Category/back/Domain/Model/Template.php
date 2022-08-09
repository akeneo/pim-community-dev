<?php
declare(strict_types=1);



namespace Akeneo\Category\Domain\Model;


use Akeneo\Category\Api\Model\Template\TemplateId;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;

/**
* @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Template
{
    public function __construct(
        private TemplateId $id,
        private Code $code,
        private LabelCollection $labelCollection,
        private ?CategoryId $parentId,
    ) {
    }

}
