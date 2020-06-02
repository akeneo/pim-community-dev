<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\Documented;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RouteMessageParameter implements MessageParameterInterface
{
    /** @var string */
    private $title;

    /** @var string */
    private $route;

    /** @var string */
    private $needle;

    /** @var array */
    private $routeParameters;

    public function __construct(string $title, string $route, string $needle, array $routeParameters = [])
    {
        $this->title = $title;
        if (1 !== preg_match('/^[a-z_]+$/', $route)) {
            throw new \InvalidArgumentException(sprintf(
                'The provided route must be composed by a-z or _ characters only, "%s" given.',
                $route
            ));
        }
        $this->route = $route;
        if (1 !== preg_match('/^{[^{}]+}$/', $needle)) {
            throw new \InvalidArgumentException(sprintf(
                '$needle must be a string surrounded by "{needle}", "%s" given.',
                $needle
            ));
        }
        $this->needle = $needle;
        foreach ($routeParameters as $key => $parameter) {
            if (!is_string($key) || !(is_string($parameter) || is_numeric($parameter))) {
                throw new \InvalidArgumentException(sprintf(
                    '$routeParameter argument from "%s" class must be an associative array of string.',
                    self::class
                ));
            }
        }
        $this->routeParameters = $routeParameters;
    }

    public function normalize(): array
    {
        return [
            'type' => MessageParameterTypes::ROUTE,
            'route' => $this->route,
            'routeParameters' => $this->routeParameters,
            'title' => $this->title,
            'needle' => $this->needle,
        ];
    }

    public function needle(): string
    {
        return $this->needle;
    }
}
