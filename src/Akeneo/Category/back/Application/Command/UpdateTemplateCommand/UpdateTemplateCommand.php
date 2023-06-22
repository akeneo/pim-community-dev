<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\UpdateTemplateCommand;

use Symfony\Component\Validator\Constraints;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateTemplateCommand
{
    /**
     * @var array<string,string|null>
     */
    #[Constraints\All([
        new Constraints\Length(['max' => 255]),
    ])]
    public readonly array $labels;

    /**
     * @param array{labels:array<string,string|null>} $data
     */
    public function __construct(
        public readonly string $templateUuid,
        array $data,
    ) {
        Assert::keyExists($data, 'labels');
        Assert::isMap($data['labels']);
        Assert::allNullOrString($data['labels']);
        $this->labels = $data['labels'];
    }
}
