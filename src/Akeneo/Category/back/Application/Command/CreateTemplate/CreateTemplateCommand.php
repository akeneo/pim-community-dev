<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\CreateTemplate;

use Akeneo\Category\Domain\ValueObject\CategoryId;
use Symfony\Component\Validator\Constraints;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateTemplateCommand
{
    #[Constraints\NotBlank]
    #[Constraints\Length(['max' => 100])]
    public readonly string $templateCode;

    /**
     * @var array<string,string|null>
     */
    #[Constraints\All([
        new Constraints\Length(['max' => 255]),
    ])]
    public readonly array $labels;

    /**
     * @param array{code:string,labels:array<string,string>} $data
     */
    public function __construct(
        readonly CategoryId $categoryTreeId,
        readonly array $data,
    ) {
        Assert::string($data['code']);
        Assert::notEmpty($data['code']);
        Assert::isMap($data['labels']);
        Assert::allNullOrString($data['labels']);
        Assert::allString($data['labels']);

        $this->templateCode = $data['code'];
        $this->labels = $data['labels'];
    }
}
