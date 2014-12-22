<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Rule repository
 *
 * TODO: should be moved in a Doctrine repo
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RuleDefinitionRepository extends EntityRepository implements RuleDefinitionRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findAllOrderedByPriority()
    {
        return $this->findBy([], ['priority' => 'DESC']);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findByReference($code)
    {
        return $this->findOneBy(['code' => $code]);
    }
}
