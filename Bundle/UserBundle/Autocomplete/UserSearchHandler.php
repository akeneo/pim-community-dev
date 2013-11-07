<?php

namespace Oro\Bundle\UserBundle\Autocomplete;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;

use Oro\Bundle\FormBundle\Autocomplete\FullNameSearchHandler;

class UserSearchHandler extends FullNameSearchHandler
{
    const IMAGINE_AVATAR_FILTER = 'avatar_med';

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @param CacheManager $cacheManager
     * @param string $userEntityName
     * @param array $properties
     */
    public function __construct(CacheManager $cacheManager, $userEntityName, array $properties)
    {
        $this->cacheManager = $cacheManager;
        parent::__construct($userEntityName, $properties);
    }

    /**
     * {@inheritdoc}
     */
    public function convertItem($user)
    {
        $result = parent::convertItem($user);
        $result['avatar'] = null;

        $imagePath = $this->getPropertyValue('imagePath', $user);
        if ($imagePath) {
            $result['avatar'] = $this->cacheManager->getBrowserPath($imagePath, self::IMAGINE_AVATAR_FILTER);
        }

        return $result;
    }
}
