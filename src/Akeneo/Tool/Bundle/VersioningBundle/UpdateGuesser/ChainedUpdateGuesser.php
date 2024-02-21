<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;

/**
 * Constraint
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChainedUpdateGuesser implements UpdateGuesserInterface
{
    /**
     * @var UpdateGuesserInterface[]
     */
    protected $guessers = [];

    /**
     * {@inheritdoc}
     */
    public function supportAction($action)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function guessUpdates(EntityManager $em, $entity, $action)
    {
        $updates = [];

        foreach ($this->guessers as $guesser) {
            if ($guesser->supportAction($action)) {
                $updates = array_merge(
                    $updates,
                    $guesser->guessUpdates($em, $entity, $action)
                );
            }
        }

        return $updates;
    }

    /**
     * {@inheritdoc}
     */
    public function addUpdateGuesser(UpdateGuesserInterface $guesser)
    {
        $this->guessers[] = $guesser;
    }
}
