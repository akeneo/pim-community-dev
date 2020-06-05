<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

final class ConcatenateAction
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
}
