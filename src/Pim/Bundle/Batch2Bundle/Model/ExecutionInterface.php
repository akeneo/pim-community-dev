<?php

namespace Pim\Bundle\Batch2Bundle\Model;

/**
 * 
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ExecutionInterface
{
    public function execute(ExecutionContext $context);
}
