<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ImportExportBundle;

use Pim\Bundle\ImportExportBundle\DependencyInjection\Compiler\RegisterJobNameVisibilityCheckerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Enterprise import export bundle overriden
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class PimEnterpriseImportExportBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new RegisterJobNameVisibilityCheckerPass(
                [
                    'pimee_workflow.job_name.csv_published_product_export',
                    'pimee_workflow.job_name.xlsx_published_product_export'
                ]
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'PimImportExportBundle';
    }
}
