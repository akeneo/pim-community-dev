<?php

namespace Pim\Bundle\VersioningBundle\Manager;

/**
 * Version manager
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionContext
{
    /**
     * Versioning context
     *
     * @var array
     */
    protected $context;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->context['default'] = null;
    }

    /**
     * Set context
     *
     * @param string      $context
     * @param string|null $fqcn
     */
    public function addContext($context, $fqcn = null)
    {
        if ($fqcn) {
            $this->context[$fqcn] = $context;
        } else {
            $this->context['default'] = $context;
        }
    }

    /**
     * Get context
     *
     * @param null $fqcn
     *
     * @return null|string
     */
    public function getContext($fqcn = null)
    {
        if (isset($this->context[$fqcn])) {
            return $this->context[$fqcn];
        }

        return $this->context['default'];
    }
}
