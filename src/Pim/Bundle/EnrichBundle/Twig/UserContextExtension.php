<?php

namespace Pim\Bundle\EnrichBundle\Twig;

use Pim\Bundle\UserBundle\Context\UserContext;

/**
 * Twig extension to expose the usercontext
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserContextExtension extends \Twig_Extension
{
    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * Constructor
     *
     * @param UserContext $userContext
     */
    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'usercontext' => new \Twig_Function_Method($this, 'getUserContext')
        ];
    }

    /**
     * Get user context
     *
     * @return []
     */
    public function getUserContext()
    {
        return [
            'user' => [
                'username'  => $this->userContext->getUser()->getUsername(),
                'firstName' => $this->userContext->getUser()->getFirstName(),
                'lastName'  => $this->userContext->getUser()->getLastName()
            ],
            'catalogLocale'  => $this->userContext->getCurrentLocale()->getCode(),
            'catalogChannel' => $this->userContext->getUserChannel()->getCode(),
            'userLocale'     => 'en_US'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_usercontext_extension';
    }
}
