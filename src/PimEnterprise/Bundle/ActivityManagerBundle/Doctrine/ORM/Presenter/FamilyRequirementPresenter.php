<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Presenter;

use PimEnterprise\Component\ActivityManager\Presenter\PresenterInterface;

/**
 * Presents the mandatory attributes coming from the ORM into a comparable data structure.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class FamilyRequirementPresenter implements PresenterInterface
{
    /** @var PresenterInterface */
    protected $presenter;

    /**
     * @param PresenterInterface $presenter
     */
    public function __construct(PresenterInterface $presenter)
    {
        $this->presenter = $presenter;
    }

    /**
     * {@inheritdoc}
     */
    public function present(array $values, array $options = [])
    {
        return $this->presenter->present($values);
    }
}
