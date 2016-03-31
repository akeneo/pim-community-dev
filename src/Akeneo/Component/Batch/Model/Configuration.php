<?php

namespace Akeneo\Component\Batch\Model;

/**
 * Class Configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class Configuration
{
    /** @var array */
    protected $parameters;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param $parameter
     *
     * @return mixed
     */
    public function getData($parameter)
    {
        return $this->parameters[$parameter];
    }
}
