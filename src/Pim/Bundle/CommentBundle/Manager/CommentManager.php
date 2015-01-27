<?php

namespace Pim\Bundle\CommentBundle\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Persistence\ObjectManager;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Pim\Bundle\CommentBundle\Model\CommentSubjectInterface;
use Pim\Bundle\CommentBundle\Model\CommentInterface;
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

    /** @var ObjectManager */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param CommentRepositoryInterface $repository
     * @param ObjectManager              $objectManager
     */
    public function __construct(CommentRepositoryInterface $repository, ObjectManager $objectManager)
    {
        $this->repository    = $repository;
        $this->objectManager = $objectManager;
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

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        if (!$object instanceof CommentInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a use Pim\Bundle\CommentBundle\Model\CommentInterface, "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $options = array_merge(['flush' => true], $options);
        $this->objectManager->persist($object);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object, array $options = [])
    {
        if (!$object instanceof CommentInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a use Pim\Bundle\CommentBundle\Model\CommentInterface, "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $options = array_merge(['flush' => true], $options);
        $this->objectManager->remove($object);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }
}
