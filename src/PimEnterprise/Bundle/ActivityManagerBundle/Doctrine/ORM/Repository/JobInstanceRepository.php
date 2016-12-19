<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PimEnterprise\Component\ActivityManager\Repository\JobInstanceRepositoryInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class JobInstanceRepository extends EntityRepository implements JobInstanceRepositoryInterface
{
    /** @var string */
    protected $projectCalculationJobName;

    /**
     * @param EntityManager $em
     * @param string        $class
     * @param string        $projectCalculationJobName
     */
    public function __construct(EntityManager $em, $class, $projectCalculationJobName)
    {
        parent::__construct($em, $em->getClassMetadata($class));

        $this->projectCalculationJobName = $projectCalculationJobName;
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectCalculation()
    {
        return $this->findOneBy(['code' => $this->projectCalculationJobName]);
    }
}
