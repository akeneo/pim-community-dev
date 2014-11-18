<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Model;

/**
 * Rule interface
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface RuleInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     *
     * @return RuleInterface
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     *
     * @return RuleInterface
     */
    public function setType($type);

    /**
     * Get rule content. For example, a JSON encoded string
     * that contains the whole configuration or a simple string
     * that is a rule expression.
     *
     * @return string
     */
    public function getContent();

    /**
     * @param mixed $content
     *
     * @return RuleInterface
     */
    public function setContent($content);

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @param int $priority
     *
     * @return RuleInterface
     */
    public function setPriority($priority);
}
