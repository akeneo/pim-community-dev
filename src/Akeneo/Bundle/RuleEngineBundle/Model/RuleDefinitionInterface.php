<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Model;

/**
 * Rule interface
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface RuleDefinitionInterface
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
     * @return RuleDefinitionInterface
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     *
     * @return RuleDefinitionInterface
     */
    public function setType($type);

    /**
     * Get rule content. In default implementation, the content
     * is stored in JSON but is transformed to array when loaded.
     *
     * @return array
     */
    public function getContent();

    /**
     * @param array $content
     *
     * @return RuleDefinitionInterface
     */
    public function setContent(array $content);

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @param int $priority
     *
     * @return RuleDefinitionInterface
     */
    public function setPriority($priority);
}
