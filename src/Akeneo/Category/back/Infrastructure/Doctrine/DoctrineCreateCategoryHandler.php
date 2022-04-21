<?php

namespace Akeneo\Category\API\Command;

class DoctrineCreateCategoryHandler
{

    public function __invoke(CreateCategoryCommand $command): void
    {

        // in infra for execution of command :
        $category = $this->factory->create();
        $this->updateCategory($category, $data, 'post_categories');
        $this->validateCategory($category);

        $this->saver->save($category);
        // in infra
    }
}
