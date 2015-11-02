<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\RuleEngine\ActionApplier;

use Akeneo\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Action applier registry
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ActionApplierRegistry implements ActionApplierRegistryInterface
{
    /** @var ActionApplierInterface[] */
    protected $actionAppliers = [];

    /**
     * {@inheritdoc}
     */
    public function getActionApplier(ActionInterface $action)
    {
        foreach ($this->actionAppliers as $actionApplier) {
            if ($actionApplier->supports($action)) {
                return $actionApplier;
            }
        }

        throw new \LogicException(
            sprintf('The action "%s" is not supported yet.', ClassUtils::getClass($action))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addActionApplier(ActionApplierInterface $actionApplier)
    {
        $this->actionAppliers[] = $actionApplier;
    }
}
