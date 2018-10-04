<?php

namespace Akeneo\Platform\Bundle\UIBundle\Resolver;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Get current locale store in request
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleResolver
{
    /** @var RequestStack */
    protected $requestStack;

    /** @var string */
    protected $defaultLocale;

    /**
     * @param RequestStack $requestStack
     * @param string       $defaultLocale
     */
    public function __construct(RequestStack $requestStack, $defaultLocale)
    {
        $this->requestStack = $requestStack;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Get current locale. If request is null, take the default locale defined in config
     *
     * @return string
     */
    public function getCurrentLocale()
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return $this->defaultLocale;
        }

        return $request->getLocale();
    }
}
