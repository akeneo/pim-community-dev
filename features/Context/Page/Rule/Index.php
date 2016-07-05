<?php

namespace Context\Page\Rule;

use Context\Page\Base\Grid;

/**
 * Rules index page
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class Index extends Grid
{
    /** @var string */
    protected $path = '/configuration/rules';


    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            [
                'Execute rules' => ['css' => '.btn .execute-all-rules'],
            ],
            $this->elements
        );
    }
}
