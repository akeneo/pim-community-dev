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

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Channel;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Channel\ChannelExistsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ChannelShouldExistValidator extends ConstraintValidator
{
    /** @var ChannelExistsInterface */
    private $channelExists;

    public function __construct(ChannelExistsInterface $channelExists)
    {
        $this->channelExists = $channelExists;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($channelIdentifier, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkChannelIdentifierType($channelIdentifier);

        if (null !== $channelIdentifier && false === ($this->channelExists)($channelIdentifier)) {
            $this->context->buildViolation(ChannelShouldExist::ERROR_MESSAGE)
                ->setParameter('channel_identifier', $channelIdentifier->normalize())
                ->atPath('channel')
                ->addViolation();
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof ChannelShouldExist) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkChannelIdentifierType($channelIdentifier): void
    {
        if (null !== $channelIdentifier && !$channelIdentifier instanceof ChannelIdentifier) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                ChannelIdentifier::class, get_class($channelIdentifier)));
        }
    }
}
