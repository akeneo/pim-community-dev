<?php

namespace Oro\Bundle\GridBundle\Route;

use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;

interface RouteGeneratorInterface
{
    /**
     * @param ParametersInterface $parameters
     * @param array $extendParameters
     * @return string
     */
    public function generateUrl(ParametersInterface $parameters = null, array $extendParameters = array());

    /**
     * @param ParametersInterface $parameters
     * @param FieldDescriptionInterface $field
     * @param string $direction
     * @return string
     * @deprecated
     */
    public function generateSortUrl(ParametersInterface $parameters, FieldDescriptionInterface $field, $direction);

    /**
     * @param ParametersInterface $parameters
     * @param int $page
     * @param int $perPage
     * @return string
     * @deprecated
     */
    public function generatePagerUrl(ParametersInterface $parameters, $page, $perPage = null);

    /**
     * Set array with route parameters
     *
     * @param array $parameters
     */
    public function setRouteParameters(array $parameters);
}
