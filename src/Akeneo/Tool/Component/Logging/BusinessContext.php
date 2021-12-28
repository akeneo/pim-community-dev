<?php


namespace Akeneo\Tool\Component\Logging;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface BusinessContext
{
    /**
     * Builds a context array used for indexing/output, made of an entity name and a sub array containing technical id and business keys
     * ['entity_name'=> ['id'=><ID>,'code'=><code>]]
     * @return array
     */
    public function getContext(): array;
}
