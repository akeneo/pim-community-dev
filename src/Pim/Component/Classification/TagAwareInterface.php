<?php

namespace Pim\Component\Classification;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Classification\Model\TagInterface;

/**
 * Implementing this interface allows to be aware of tags
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface TagAwareInterface
{
    /**
     * @return ArrayCollection of TagInterface
     */
    public function getTags();

    /**
     * @param TagInterface $tag
     *
     * @return mixed
     */
    public function removeTag(TagInterface $tag);

    /**
     * @param TagInterface $tag
     *
     * @return mixed
     */
    public function addTag(TagInterface $tag);

    /**
     * Get a string with tags linked to the entity
     *
     * @return string
     */
    public function getTagCodes();
}
