<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Voter;

use Akeneo\Component\Batch\Model\JobInstance;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Job profile voter, allows to know if a job profile can be executed or edited by
 * a user depending on his user groups
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class JobProfileVoter extends Voter implements VoterInterface
{
    /** @var JobProfileAccessManager */
    protected $accessManager;

    /**
     * Constructor
     *
     * @param JobProfileAccessManager $accessManager
     */
    public function __construct(JobProfileAccessManager $accessManager)
    {
        $this->accessManager = $accessManager;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        if (!$object instanceof JobInstance) {
            return $result;
        }

        foreach ($attributes as $attribute) {
            if ($this->supports($attribute, $object)) {
                $result = VoterInterface::ACCESS_DENIED;

                if ($this->voteOnAttribute($attribute, $object, $token)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return $result;
    }

    /**
     * Get user groups for specific job profile
     *
     * @param string      $attribute
     * @param JobInstance $object
     *
     * @return \Oro\Bundle\UserBundle\Entity\Group[]
     */
    protected function extractGroups($attribute, $object)
    {
        if ($attribute === Attributes::EDIT) {
            $grantedGroups = $this->accessManager->getEditUserGroups($object);
        } else {
            $grantedGroups = $this->accessManager->getExecuteUserGroups($object);
        }

        return $grantedGroups;
    }


    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [Attributes::EXECUTE, Attributes::EDIT]) &&
            $subject instanceof JobInstance;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $grantedGroups = $this->extractGroups($attribute, $subject);

        foreach ($grantedGroups as $group) {
            if ($token->getUser()->hasGroup($group)) {
                return true;
            }
        }

        return false;
    }
}
