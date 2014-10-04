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
class RuleSubjectSet implements RuleSubjectSetInterface
{
    protected $code;
    protected $type;
    protected $subjects = [];

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getSubjects()
    {
        return $this->subjects;
    }

    public function setSubjects(array $subjects)
    {
        $this->subjects = $subjects;

        return $this;
    }
}
