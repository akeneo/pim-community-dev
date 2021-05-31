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

namespace Akeneo\AssetManager\Infrastructure\Validation\Channel;

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Query\Channel\ChannelExistsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ChannelShouldExistValidator extends ConstraintValidator
{
    private ChannelExistsInterface $channelExists;

    public function __construct(ChannelExistsInterface $channelExists)
    {
        $this->channelExists = $channelExists;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($channelReference, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkChannelReferenceType($channelReference);

        if (!$channelReference->isEmpty() && !$this->channelExists->exists($channelReference->getIdentifier())) {
            $this->context->buildViolation(ChannelShouldExist::ERROR_MESSAGE)
                ->setParameter('channel_identifier', $channelReference->normalize())
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
    private function checkChannelReferenceType($channelReference): void
    {
        if (null !== $channelReference && !$channelReference instanceof ChannelReference) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                ChannelIdentifier::class, get_class($channelReference)));
        }
    }
}
