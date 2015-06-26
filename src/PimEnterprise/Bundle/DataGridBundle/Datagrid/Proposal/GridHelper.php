<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Proposal;

use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;

/**
 * Helper for proposal datagrid
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class GridHelper
{
    /** @var ProductDraftRepositoryInterface $draftRepository */
    protected $draftRepository;

    /**
     * @param ProductDraftRepositoryInterface $draftRepository
     */
    public function __construct(ProductDraftRepositoryInterface $draftRepository)
    {
        $this->draftRepository = $draftRepository;
    }
    /**
     * Returns available proposal author choices (author can be user or job instance)
     *
     * @return array
     */
    public function getAuthorChoices()
    {
        $authors = $this->draftRepository->getDistinctAuthors();
        $choices = array_combine($authors, $authors);

        return $choices;
    }
}
