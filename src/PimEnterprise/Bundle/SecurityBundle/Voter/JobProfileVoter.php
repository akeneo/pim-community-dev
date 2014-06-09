<?php

namespace PimEnterprise\Bundle\SecurityBundle\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * TODO
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
}
