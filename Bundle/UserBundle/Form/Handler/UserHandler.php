<?php

namespace Oro\Bundle\UserBundle\Form\Handler;

use Oro\Bundle\TagBundle\Entity\TagManager;
use Oro\Bundle\TagBundle\Form\Handler\TagHandlerInterface;
use Oro\Bundle\UserBundle\Entity\User;

class UserHandler extends AbstractUserHandler implements TagHandlerInterface
{
    /**
     * @var TagManager
     */
    protected $tagManager;

    protected function onSuccess(User $user)
    {
        $this->manager->updateUser($user);
        $this->tagManager->saveTagging($user);

        // Reloads the user to reset its username. This is needed when the
        // username or password have been changed to avoid issues with the
        // security layer.
        $this->manager->reloadUser($user);
    }

    public function setTagManager(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
    }
}
