<?php

namespace Oro\Bundle\TagBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FPN\TagBundle\Entity\Tag as BaseTag;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * Tag
 *
 * @ORM\Table(name="oro_tag_tag")
 * @ORM\Entity
 */
class Tag extends BaseTag
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Tagging", mappedBy="tag", fetch="EAGER")
     */
    protected $tagging;
}
