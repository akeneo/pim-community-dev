<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

use Webmozart\Assert\Assert;

final class ConcatenateAction implements ActionInterface
{
    public $from;
    public $to;

    public function __construct(array $data)
    {
        $from = $data['from'] ?? null;
        if (is_array($from)) {
            $from = array_map(
                function ($source) {
                    return is_array($source) ? new ProductSource($source) : $source;
                },
                $from
            );
        }
        $this->from = $from;

        $to = $data['to'] ?? null;
        if (is_array($to)) {
            $to = new ProductTarget($to);
        }
        $this->to = $to;
    }

    public function toArray(): array
    {
        Assert::isArray($this->from);
        Assert::allIsInstanceOf($this->from, ProductSource::class);
        Assert::isInstanceOf($this->to, ProductTarget::class);

        return [
            'type' => 'concatenate',
            'from' => array_map(function (ProductSource $source): array {
                return $source->toArray();
            }, $this->from),
            'to' => $this->to->toArray(),
        ];
    }
}
