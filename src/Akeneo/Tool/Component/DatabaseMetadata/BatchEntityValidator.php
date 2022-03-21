<?php


namespace Akeneo\Tool\Component\DatabaseMetadata;

use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\JobInstanceValidator;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextFactoryInterface;
use Symfony\Component\Validator\Validator\RecursiveContextualValidator;
use Symfony\Component\Validator\Validator\RecursiveValidator;

/**
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchEntityValidator
{
    private EntityRepository $jobInstanceRepository;
    private array $constraints=[];
    private RecursiveValidator $recursiveValidator;

    public function __construct(EntityRepository $jobInstanceRepository, array $contraintClasses, RecursiveValidator $recursiveValidator)
    {
        $this->jobInstanceRepository = $jobInstanceRepository;
        foreach ($contraintClasses as $constraintClass) {
            $this->constraints[] = new $constraintClass;
        }
        $this->recursiveValidator = $recursiveValidator;
    }

    public function validateAll(): array
    {
        $constraintViolationList=[];
        foreach ($this->jobInstanceRepository->findAll() as $entity) {
            $constraintViolationList[] = $this->recursiveValidator->validate($entity, $this->constraints);
        }
        return $constraintViolationList;
    }

    public function getJobInstanceRepository(): EntityRepository
    {
        return $this->jobInstanceRepository;
    }
}
