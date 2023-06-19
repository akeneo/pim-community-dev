<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\CreateTemplate;

use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateTemplateCommand
{
    public readonly TemplateCode $templateCode;
    public readonly LabelCollection $labels;

    /**
     * @param array{code:string,labels:array<string,string>} $data
     */
    public function __construct(
        readonly CategoryId $categoryTreeId,
        readonly array $data,
    ) {
        Assert::string($data['code']);
        Assert::notEmpty($data['code']);
        Assert::allString($data['labels']);

        $this->labels = LabelCollection::fromArray($data['labels']);
        $this->templateCode = new TemplateCode($data['code']);
    }
}
