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

class RuleInstance implements RuleInstanceInterface
{
    protected $id;

    protected $code;

    protected $ruleFqcn;

    protected $content;

    public function getId()
    {
        return $this->id;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getRuleFQCN()
    {
        return $this->ruleFqcn;
    }

    /**
     * string JSON encoded content
     */
    public function getContent()
    {
        return $this->content;
    }
}
