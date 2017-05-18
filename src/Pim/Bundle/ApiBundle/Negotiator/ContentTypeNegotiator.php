<?php

namespace Pim\Bundle\ApiBundle\Negotiator;

use FOS\RestBundle\Util\StopFormatListenerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * Content type negotiator to get the allowed content types for a given request,
 * thanks to symfony request matcher.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ContentTypeNegotiator implements ContentTypeNegotiatorInterface
{
    /** @var array */
    protected $map = [];

    /**
     * {@inheritdoc}
     */
    public function getContentTypes(Request $request)
    {
        foreach ($this->map as $elements) {
            if (!$elements['request_matcher']->matches($request)) {
                continue;
            }

            $rule = $elements['rule'];

            if (!empty($rule['stop'])) {
                throw new StopFormatListenerException('Stopped content type negotiator');
            }

            return $rule['content_types'];
        }
    }

    /**
     * Add a request matcher and the associated rule.
     *
     * @param RequestMatcherInterface $requestMatcher
     * @param array                   $rule
     */
    public function add(RequestMatcherInterface $requestMatcher, array $rule)
    {
        $this->map[] = ['request_matcher' => $requestMatcher, 'rule' => $rule];
    }
}
