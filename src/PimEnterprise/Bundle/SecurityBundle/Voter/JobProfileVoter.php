<?php

namespace PimEnterprise\Bundle\SecurityBundle\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;

/**
 * Job profile voter, allows to know if a job profile can be executed or edited by
 * a user depending on his roles
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class JobProfileVoter implements VoterInterface
{
    /** @staticvar string */
    const EXECUTE_JOB_PROFILE = 'EXECUTE_JOB_PROFILE';

    /** @staticvar string */
    const EDIT_JOB_PROFILE    = 'EDIT_JOB_PROFILE';

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
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array(self::EXECUTE_JOB_PROFILE, self::EDIT_JOB_PROFILE));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class instanceof JobInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        if ($this->supportsClass($object)) {
            foreach ($attributes as $attribute) {
                if ($this->supportsAttribute($attribute)) {
                    $result = VoterInterface::ACCESS_DENIED;
                    $grantedRoles = $this->extractRoles($attribute, $object);
                }

                foreach ($grantedRoles as $role) {
                    if ($token->getUser()->hasRole($role)) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get roles for specific job profile
     *
     * @param string      $attribute
     * @param JobInstance $object
     *
     * @return Role[]
     */
    protected function extractRoles($attribute, $object)
    {
        if ($attribute === self::EDIT_JOB_PROFILE) {
            $grantedRoles = $this->accessManager->getEditRoles($object);
        } else {
            $grantedRoles = $this->accessManager->getExecuteRoles($object);
        }

        return $grantedRoles;
    }
}
