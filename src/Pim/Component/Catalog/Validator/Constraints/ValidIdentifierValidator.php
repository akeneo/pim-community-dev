<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\Process\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validator for ValidIdentifier constraint
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidIdentifierValidator extends ConstraintValidator
{
    /** @var  ExecutionContextInterface */
    protected $executionContext;
    
    /** @var IdentifiableObjectRepositoryInterface */
    protected $identifiableObjectRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $identifiableObjectRepository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $identifiableObjectRepository) 
    {
        $this->identifiableObjectRepository = $identifiableObjectRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($identifiers, Constraint $constraint)
    {
        if (!$constraint instanceof ValidIdentifier) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\ValidIdentifier');
        }

        if (empty($identifiers)) {
            return;
        }

        $identifiers = explode(',', $identifiers);

        $invalidIdentifiers = [];
        foreach ($identifiers as $identifier) {
            if (null === $this->identifiableObjectRepository->findOneByIdentifier($identifier)) {
                $invalidIdentifiers[] = $identifier;
            }
        }
        
        if (!empty($invalidIdentifiers)) {
            $message = sprintf($constraint->message, implode(', ', $invalidIdentifiers));
            $this->context->buildViolation($message)->addViolation();
        }
    }
}
