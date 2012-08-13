<?php
namespace Strixos\DataFlowBundle\Model\Extract;

use Strixos\DataFlowBundle\Entity\Step;
/**
 *
 * @author    Nicolas Dupont @ Strixos
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class FileReader extends Step
{

    protected $_filepath;
}