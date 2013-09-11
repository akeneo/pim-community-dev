<?php

namespace Pim\Bundle\GridBundle\Action\Export;

interface ExportActionInterface
{
    public function getAclResource();

    public function getLabel();

    public function getName();

    public function getRoute();

    public function getOptions();

    public function getOption($name);
}
