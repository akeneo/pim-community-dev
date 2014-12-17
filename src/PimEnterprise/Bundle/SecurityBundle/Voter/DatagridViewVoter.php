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

use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\DatagridViewAccessManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Datagrid view voter, allows to know if a datagrid view is usable by the current user.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class DatagridViewVoter implements VoterInterface
{
    /** @var DatagridViewAccessManager */
    protected $accessManager;

    /**
     * Constructor
     *
     * @param DatagridViewAccessManager $accessManager
     */
    public function __construct(DatagridViewAccessManager $accessManager)
    {
        $this->accessManager = $accessManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, [Attributes::VIEW]);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class instanceof DatagridView;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        if (!$this->supportsClass($object)) {
            return $result;
        }

        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute)) {
                $result = VoterInterface::ACCESS_DENIED;

                if ($this->accessManager->isUserGranted($token->getUser(), $object, $attribute)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return $result;
    }
}
