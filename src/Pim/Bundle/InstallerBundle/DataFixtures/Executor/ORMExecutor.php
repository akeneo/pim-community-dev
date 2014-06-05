<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor as BaseORMExecutor;

/**
 * Class responsible for executing data fixtures.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMExecutor extends BaseORMExecutor
{
    /** @inheritDoc */
    public function execute(array $fixtures, $append = false)
    {
        $executor = $this;

        // avoid to have only one transaction
        foreach ($fixtures as $fixture) {
            $executor->load($this->getObjectManager(), $fixture);
        }
    }
}
