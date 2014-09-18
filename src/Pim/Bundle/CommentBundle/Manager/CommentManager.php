<?php

namespace Pim\Bundle\CommentBundle\Manager;

use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CommentBundle\Model\CommentSubjectInterface;
use Pim\Bundle\CommentBundle\Repository\CommentRepositoryInterface;

/**
 * Comment manager
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommentManager
{
    /** @var CommentRepositoryInterface */
    protected $repository;

    /**
     * Constructor
     *
     * @param CommentRepositoryInterface $repository
     */
    public function __construct(CommentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get the comments related to a subject
     *
     * @param CommentSubjectInterface $subject
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|\Pim\Bundle\CommentBundle\Model\CommentInterface[]
     */
    public function getComments(CommentSubjectInterface $subject)
    {
        return $this->repository->getComments(
            ClassUtils::getClass($subject),
            $subject->getId()
        );
    }
}
