<?php

namespace Oro\Bundle\UserBundle\EntityAutocomplete\Transformer;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Oro\Bundle\FormBundle\EntityAutocomplete\Transformer\EntityPropertiesTransformer;

class UserTransformer extends EntityPropertiesTransformer
{
    const IMAGINE_AVATAR_FILTER = 'avatar_med';

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
        parent::__construct(array('username', 'firstName', 'lastName', 'email'));
    }

    /**
     * {@inheritdoc}
     */
    public function transform($user)
    {
        $result = parent::transform($user);
        $result['avatar'] = null;

        $imagePath = $this->getPropertyValue('imagePath', $user);
        if ($imagePath) {
            $result['avatar'] = $this->cacheManager->getBrowserPath($imagePath, self::IMAGINE_AVATAR_FILTER);
        }

        return $result;
    }
}
