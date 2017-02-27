<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Remover;

use Doctrine\ORM\EntityRepository;

/**
 * Chained project remover rule
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ChainedProjectRemover implements ProjectRemoverInterface
{
    /** @var ProjectRemoverInterface[] */
    protected $removers = [];

    /**
     * @param array $removers
     */
    public function __construct(array $removers)
    {
        $this->removers = $removers;
    }

    /**
     * {@inheritdoc}
     */
    public function removeProjectsImpactedBy($entity, $action = null)
    {
        foreach ($this->removers as $remover) {
            if ($remover->isSupported($entity, $action)) {
                $remover->removeProjectsImpactedBy($entity, $action);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($entity, $action = null)
    {
        return true;
    }
}
