<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Voter;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Job profile voter, allows to know if a job profile can be executed or edited by
 * a user depending on the attributes and locales in the sources of the job
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class JobProfileVoter extends Voter implements VoterInterface
{
    protected Voter $decoratedVoter;
    protected CanEditTailoredExport $canEditTailoredExport;
    /** @var string[] */
    private array $tailoredExportJobNames;

    public function __construct(
        Voter $decoratedVoter,
        CanEditTailoredExport $canEditTailoredExport,
        array $tailoredExportJobNames
    ) {
        $this->decoratedVoter = $decoratedVoter;
        $this->canEditTailoredExport = $canEditTailoredExport;
        $this->tailoredExportJobNames = $tailoredExportJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        if (!$subject instanceof JobInstance) {
            return $result;
        }

        $vote = $this->decoratedVoter->vote($token, $subject, $attributes);

        if (
            VoterInterface::ACCESS_DENIED === $vote
            || !in_array($subject->getJobName(), $this->tailoredExportJobNames)
        ) {
            return $vote;
        }

        foreach ($attributes as $attribute) {
            if ($this->supports($attribute, $subject)) {
                $result = VoterInterface::ACCESS_DENIED;

                if ($this->voteOnAttribute($attribute, $subject, $token)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $this->decoratedVoter->supports($attribute, $subject);
    }

    protected function voteOnAttribute($attribute, $object, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            throw new \InvalidArgumentException('Invalid user type');
        }

        $userId = $user->getId();
        if (null === $user->getId()) {
            return false;
        }

        return $this->canEditTailoredExport->execute($object, $userId);
    }
}
