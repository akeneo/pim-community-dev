<?php

namespace Pim\Bundle\RulesEngineBundle\Entity;

interface RuleInstanceInterface
{
    public function getId();

    public function getCode();

    public function getRuleFQCN();

    public function getContent();
}
