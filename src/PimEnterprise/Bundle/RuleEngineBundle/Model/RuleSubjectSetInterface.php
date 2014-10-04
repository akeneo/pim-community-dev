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
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface RuleSubjectSetInterface
{
    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     *
     * @return RuleSubjectSetInterface
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param $type
     *
     * @return RuleSubjectSetInterface
     */
    public function setType($type);

    /**
     * @return array
     */
    public function getSubjects();

    /**
     * @param array $subjects
     *
     * @return RuleSubjectSetInterface
     */
    public function setSubjects(array $subjects);
}
