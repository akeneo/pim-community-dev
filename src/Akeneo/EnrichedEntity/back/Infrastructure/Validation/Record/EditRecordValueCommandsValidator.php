<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Record;

use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditRecordValueCommandsValidator extends ConstraintValidator
{
    /** @var ValidatorInterface */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate($editRecordCommand, Constraint $constraint)
    {
        if (!$constraint instanceof EditRecordValueCommands) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        if (!$editRecordCommand instanceof EditRecordValueCommands) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                EditRecordCommand::class, get_class($editRecordCommand)));
        }

        if (empty($editRecordCommand->editRecordValueCommands)) {
            $this->context->addViolation('There should be updates to perform on the attribute. None found.');
        }

        foreach ($editRecordCommand->editRecordValueCommands as $editRecordValueCommand) {
            $this->validator->validate($editRecordValueCommand);
        }
    }
}
