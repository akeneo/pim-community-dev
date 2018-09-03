<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Manager;

/**
 * Version context
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionContext
{
    /** @var array */
    protected $mapping;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mapping['default'] = null;
    }

    /**
     * Add context
     *
     * @param string|null $info
     * @param string      $context
     */
    public function addContextInfo($info, $context = null)
    {
        if ($context) {
            $this->mapping[$context] = $info;
        } else {
            $this->mapping['default'] = $info;
        }
    }

    /**
     * Get context
     *
     * @param string|null $context
     *
     * @return null|string
     */
    public function getContextInfo($context = null)
    {
        if (isset($this->mapping[$context])) {
            return $this->mapping[$context];
        }

        return $this->mapping['default'];
    }

    /**
     * Get context
     *
     * @param string $context
     */
    public function unsetContextInfo($context)
    {
        if (isset($this->mapping[$context])) {
            unset($this->mapping[$context]);
        }
    }
}
