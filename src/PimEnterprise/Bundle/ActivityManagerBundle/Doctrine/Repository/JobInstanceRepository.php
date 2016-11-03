<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\Doctrine\Repository;

use Akeneo\ActivityManager\Component\Repository\JobInstanceRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class JobInstanceRepository extends EntityRepository implements JobInstanceRepositoryInterface
{
    /** @var string */
    private $projectCalculationJobName;

    public function __construct(EntityManager $em, $class, $projectCalculationJobName)
    {
        parent::__construct($em, $em->getClassMetadata($class));
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectCalculation()
    {
        return $this->findOneBy(['code' => $this->projectCalculationJobName]);
    }
}
