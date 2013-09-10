<?php

namespace Oro\Bundle\TagBundle\Entity;

interface Taggable
{
    /**
     * Returns the unique taggable resource identifier
     *
     * @return string
     */
    public function getTaggableId();

    /**
     * Returns the collection of tags for this Taggable entity
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTags();

    /**
     * Set tag collection
     *
     * @param $tags
     * @return $this
     */
    public function setTags($tags);
}
