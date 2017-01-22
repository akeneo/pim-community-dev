<?php

namespace spec\Pim\Component\Classification;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Classification\CategoryBuilder;
use Pim\Component\Classification\CategoryBuilderInterface;
use Pim\Component\Classification\CategoryInterface;
use Pim\Component\Template\TemplateInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CategoryFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CategoryBuilder::class);
    }

    function it_is_a_category_builder()
    {
        $this->shouldImplement(CategoryBuilderInerface::class);
    }

    function it_builds_a_category_from_a_template(TemplateInterface $categoryTemplate)
    {
        $category = $this->create(
            $categoryTemplate,
            [
                'code' => 'clothing',
                'parent' => 'master',
                'labels' => [
                    'fr_FR' => 'T-shirt super beau',
                    'en_US' => 'T-shirt very beautiful',
                ],
                'picture' => '/path/to/picture.png'
            ]
        );

        $category->shouldHaveType(CategoryInterface::class);
        $category->getIdentifier()->__toString()->shouldReturn('clothing');
        $properties = $category->getProperties();
        $properties->shouldHaveType(ArrayCollection::class);
    }
}
