<?php

namespace Oro\Bundle\TagBundle\Form\Handler;

use Oro\Bundle\TagBundle\Entity\TagManager;

interface TagHandlerInterface
{
    public function setTagManager(TagManager $tagManager);
}
