<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TemplatedErrorMessage
{
    /** @var string */
    private $template;

    /** @var array */
    private $parameters;

    /**
     * @param string $template Message template with the following delimiters {} around parameters, e.g. {param}.
     * @param array<string, string> $parameters Key, value pairs of the parameters defined in the message template,
     *                                          without delimiters around the key, e.g. ['param' => 'value']
     */
    public function __construct(string $template, array $parameters)
    {
        foreach ($parameters as $key => $value) {
            if (false === is_string($value)) {
                throw new \InvalidArgumentException(sprintf(
                    'Message parameter "{%s}" must be of type string, %s given.',
                    $key,
                    gettype($value)
                ));
            }
            if (1 !== substr_count($template, sprintf('{%s}', $key))) {
                throw new \InvalidArgumentException(sprintf(
                    'Message parameter "{%s}" was not found in the message template "%s".',
                    $key,
                    $template
                ));
            }
        }

        $this->template = $template;
        $this->parameters = $parameters;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function __toString(): string
    {
        $message = $this->template;
        foreach ($this->parameters as $key => $value) {
            $message = str_replace(sprintf('{%s}', $key), $value, $message);
        }

        return $message;
    }
}
