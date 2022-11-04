<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Voter;

use Akeneo\Pim\Permission\Bundle\Manager\DatagridViewAccessManager;
use Akeneo\Pim\Permission\Component\Attributes;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Datagrid view voter, allows to know if a datagrid view is usable by the current user.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class DatagridViewVoter extends Voter implements VoterInterface
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
    public function vote(TokenInterface $token, $object, array $attributes): int
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        if (!$object instanceof DatagridView) {
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
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [Attributes::VIEW]) && $subject instanceof DatagridView;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->accessManager->isUserGranted($token->getUser(), $subject, $attribute);
    }
}
