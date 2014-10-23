<?php

namespace Pim\Component\Resource\Event;

use Pim\Component\Resource\ResourceInterface;
use Pim\Component\Resource\ResourceSetInterface;
use Symfony\Component\EventDispatcher\Event as BaseEvent;

/**
 * Abstract resource event
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractResourceEvent extends BaseEvent implements ResourceEventInterface
{
    /** @var ResourceInterface|ResourceSetInterface */
    protected $subject;

    /** @var string */
    protected $subjectClass;

    /**
     * @param string $subjectClass
     */
    public function __construct($subjectClass)
    {
        $this->subjectClass = $subjectClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectClass()
    {
        return $this->subjectClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * {@inheritdoc}
     */
    public function setSubject($subject)
    {
        if (!$subject instanceof ResourceInterface && !$subject instanceof ResourceSetInterface) {
            throw new \InvalidArgumentException(
                'Subject should be an instance of "ResourceInterface" or "ResourceSetInterface".'
            );
        }

        $this->subject = $subject;

        return $this;
    }
}
