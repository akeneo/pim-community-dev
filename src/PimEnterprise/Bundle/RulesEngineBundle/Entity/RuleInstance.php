<?php

namespace Pim\Bundle\RulesEngineBundle\Entity;

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
