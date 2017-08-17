<?php
declare(strict_types=1);

namespace Pim\Component\Connector\Processor\Denormalization\AttributeFilter;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeFilter
{
    /**
     * @param array $item
     *
     * @return array
     */
    public function filter(array $item): array;
}
