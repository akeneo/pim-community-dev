<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\Documentation;

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

    public function __construct(string $title, string $href)
    {
        $this->title = $title;
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

    /**
     * @return array{type: MessageParameterTypes::HREF, href: string, title: string}
     */
    public function normalize(): array
    {
        return [
            'type' => MessageParameterTypes::HREF,
            'href' => $this->href,
            'title' => $this->title,
        ];
    }
}
