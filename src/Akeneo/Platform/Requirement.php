<?php

namespace Akeneo\Platform;

/**
 * Simple value object to transfer requirements information to the requirement checker system
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Requirement
{
    /* @var boolean */
    private $fullfilled;

    /* @var string */
    private $testMessage;

    /* @var string */
    private $helpText;

    /* @var boolean */
    private $mandatory;

    public function __construct(bool $fullfilled, string $testMessage, string $helpText, bool $mandatory = true)
    {
        $this->fullfilled = $fullfilled;
        $this->testMessage = $testMessage;
        $this->helpText = $helpText;
        $this->mandatory = $mandatory;
    }

    public function isFullfilled(): bool
    {
        return $this->fullfilled;
    }

    public function getTestMessage(): string
    {
        return $this->testMessage;
    }

    public function getHelpText(): string
    {
        return $this->helpText;
    }

    public function isMandatory(): bool
    {
        return $this->mandatory;
    }
}
