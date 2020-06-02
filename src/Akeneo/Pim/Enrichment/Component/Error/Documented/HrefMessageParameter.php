<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\Documented;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HrefMessageParameter implements MessageParameterInterface
{
    /** @var string */
    private $title;

    /** @var string */
    private $href;

    /** @var string */
    private $needle;

    public function __construct(string $title, string $href, string $needle)
    {
        $this->title = $title;
        if (1 !== preg_match('/^{[^{}]+}$/', $needle)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '$needle must be a string surrounded by "{needle}", "%s" given.',
                    $needle
                )
            );
        }
        $this->needle = $needle;
        if (false === filter_var($href, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Class "%s" need an URL as href argument, "%s" given.',
                    self::class,
                    $href
                )
            );
        }
        $this->href = $href;
    }

    public function normalize(): array
    {
        return [
            'type' => MessageParameterTypes::HREF,
            'href' => $this->href,
            'title' => $this->title,
            'needle' => $this->needle,
        ];
    }

    public function needle(): string
    {
        return $this->needle;
    }
}
