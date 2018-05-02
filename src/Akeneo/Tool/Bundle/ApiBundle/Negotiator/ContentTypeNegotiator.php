<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Negotiator;

use FOS\RestBundle\Util\StopFormatListenerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * Content type negotiator aims to get the allowed content types for a given request.
 *
 * FosRestBundle allows to provide the best accept type given a request.
 * The goal of this class is to do the same thing for the content types.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ContentTypeNegotiator
{
    /** @var array */
    protected $rulesByPriority = [];

    /**
     * Returns the allowed content types for a given request.
     *
     * @param Request $request
     *
     * @throws StopFormatListenerException
     *
     * @return string[] array of content types
     */
    public function getContentTypes(Request $request)
    {
        ksort($this->rulesByPriority);
        foreach ($this->rulesByPriority as $rules) {
            foreach ($rules as $rule) {
                if (!$rule['request_matcher']->matches($request)) {
                    continue;
                }

                $rule = $rule['rule'];

                if (!empty($rule['stop'])) {
                    throw new StopFormatListenerException('Stopped content type negotiator');
                }

                return $rule['content_types'];
            }
        }

        return [];
    }

    /**
     * Add a request matcher and the associated rule.
     *
     * @param RequestMatcherInterface $requestMatcher
     * @param array                   $rule
     */
    public function add(RequestMatcherInterface $requestMatcher, array $rule)
    {
        $this->rulesByPriority[$rule['priority']][] = ['request_matcher' => $requestMatcher, 'rule' => $rule];
    }
}
