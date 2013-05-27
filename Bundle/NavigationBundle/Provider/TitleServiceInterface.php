<?php

namespace Oro\Bundle\NavigationBundle\Provider;

interface TitleServiceInterface
{
    /**
     * Set properties from array
     *
     * @param array $values
     * @return $this
     */
    public function setData(array $values);

    /**
     * Return rendered translated title
     *
     * @param array $params
     * @param null  $title
     * @param null  $prefix
     * @param null  $suffix
     * @return $this
     */
    public function render($params = array(), $title = null, $prefix = null, $suffix = null);

    /**
     * Load title template from database
     *
     * @param string $route
     */
    public function loadByRoute($route);

    /**
     * Return serialized title data
     *
     * @return string
     */
    public function getSerialized();

    /**
     * Updates title index
     *
     * @param array $routes
     */
    public function update($routes);
}
