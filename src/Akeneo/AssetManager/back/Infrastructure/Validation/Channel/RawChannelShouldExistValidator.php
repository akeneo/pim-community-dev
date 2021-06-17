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

use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Query\Channel\ChannelExistsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validation;

class RawChannelShouldExistValidator extends ConstraintValidator
{
    private ChannelExistsInterface $channelExists;

    public function __construct(ChannelExistsInterface $channelExists)
    {
        $this->channelExists = $channelExists;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($rawChannel, Constraint $constraint)
    {
        if (null === $rawChannel) {
            return;
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($rawChannel, new Assert\Type('string'));
        foreach ($violations as $violation) {
            $this->context->addViolation($violation->getMessage(), $violation->getParameters());
            return;
        }

        $channelIdentifier = ChannelIdentifier::fromCode($rawChannel);
        if (!$this->channelExists->exists($channelIdentifier)) {
            $this->context->buildViolation(ChannelShouldExist::ERROR_MESSAGE)
                ->setParameter('channel_identifier', $channelIdentifier->normalize())
                ->atPath('channel')
                ->addViolation();
        }
    }
}
