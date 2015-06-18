<?php

namespace Pim\Bundle\CommentBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
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
class CommentManager implements SaverInterface, RemoverInterface
{
    /** @var CommentRepositoryInterface */
    protected $repository;

    /** @var SaverInterface */
    protected $saver;

    /** @var RemoverInterface */
    protected $remover;

    /**
     * Constructor
     *
     * @param CommentRepositoryInterface $repository
     * @param SaverInterface             $saver
     * @param RemoverInterface           $remover
     */
    public function __construct(
        CommentRepositoryInterface $repository,
        SaverInterface $saver,
        RemoverInterface $remover
    ) {
        $this->repository = $repository;
        $this->saver      = $saver;
        $this->remover    = $remover;
    }

    /**
     * Get the comments related to a subject
     *
     * @param CommentSubjectInterface $subject
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|\Pim\Bundle\CommentBundle\Model\CommentInterface[]
     *
     * @deprecated Will be removed in 1.5, please use CommentRepositoryInterface::getComments() instead.
     */
    public function getComments(CommentSubjectInterface $subject)
    {
        return $this->repository->getComments(
            ClassUtils::getClass($subject),
            $subject->getId()
        );
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.5 please use SaverInterface::save
     */
    public function save($object, array $options = [])
    {
        $this->saver->save($object, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.5 please use RemoverInterface::remove
     */
    public function remove($object, array $options = [])
    {
        $this->remover->remove($object, $options);
    }
}
