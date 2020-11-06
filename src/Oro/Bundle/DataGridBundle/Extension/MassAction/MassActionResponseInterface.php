<?php

namespace Oro\Bundle\DataGridBundle\Extension\MassAction;

interface MassActionResponseInterface
{
    public function isSuccessful(): bool;

    public function getOptions(): array;

    public function getMessage(): string;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getOption(string $name);
}
