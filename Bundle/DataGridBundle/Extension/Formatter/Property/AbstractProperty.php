<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

abstract class AbstractProperty implements PropertyInterface
{
    /** @var array */
    protected $params;

    /**
     * {@inheritdoc}
     */
    public function init(array $params)
    {
        $this->params = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $frontendOptions = $this->getOr(self::FRONTEND_OPTIONS_KEY, []);
        $frontendOptions = array_merge(
            [
                // use field name if label not set
                'label'      => ucfirst($this->get('name')),
                'renderable' => true,
                'editable'   => false
            ],
            $frontendOptions
        );
        $metadata        = [
            self::METADATA_TYPE_KEY             => $this->getOr(self::FRONTEND_TYPE_KEY, self::TYPE_TEXT),
            self::METADATA_OPTIONS_KEY => $frontendOptions
        ];

        return $metadata;
    }

    /**
     * Get param or throws exception
     *
     * @param string $paramName
     *
     * @throws \LogicException
     * @return mixed
     */
    protected function get($paramName)
    {
        if (!isset($this->params[$paramName])) {
            throw new \LogicException(sprintf('Trying to access not existing parameter: "%s"', $paramName));
        }

        return $this->params[$paramName];
    }

    /**
     * Get param if exists or default value
     *
     * @param string $paramName
     * @param null   $default
     *
     * @return mixed
     */
    protected function getOr($paramName, $default = null)
    {
        return isset($this->params[$paramName]) ? $this->params[$paramName] : $default;
    }
}
