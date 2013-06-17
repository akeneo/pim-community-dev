<?php

namespace Oro\Bundle\FormBundle\Tests\Functional\TestBundle\Autocomplete;

use Oro\Bundle\FormBundle\EntityAutocomplete\SearchHandlerInterface;
use Oro\Bundle\FormBundle\Tests\Functional\TestBundle\Entity\User;

class UserSearchHandler implements SearchHandlerInterface
{
    private $users;

    public function __construct()
    {
        $this->users = array();
        for ($i = 1; $i <= 10; ++$i) {
            $user = new User();
            $user->setId($i);
            $user->setUsername('User #' . $i);
            $this->users[] = $user;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function search($search, $firstResult, $maxResults)
    {
        return array_slice($this->users, $firstResult, $maxResults);
    }
}
