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
     * @param string $parameter
     *
     * @return mixed
     *
     * @throws \UndefinedParameterException
     */
    public function getParameter($parameter)
    {
        if (!isset($this->parameters[$parameter])) {
            throw new \UndefinedParameterException(sprintf('The parameter "%s" is undefined', $parameter));
        }

        return $this->parameters[$parameter];
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
