<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils;

use Monolog\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;

//TODO move to logging bundle
/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StackedContextProcessor implements ProcessorInterface
{
    //$each array element is an array itself
    private array $stack = [];

    public function __invoke(array $record)
    {
        $context = [];
        foreach ($this->stack as $stackElement) {
            foreach ($stackElement as $key => $value) {
                $record['context'][$key]=$value;//There might be a risk of overriding, unless we have adequate/strict naming conventions, otherwise we can scope, using an 'extra_context'...
            }
        }
        return $record;
    }

    public function push(array $context)
    {
        $this->stack[]=$context;
    }

    public function pop()
    {
        array_pop($this->stack);
    }
}
