<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\Project;

use AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\TeamworkAssistantTestCase;
use PHPUnit\Framework\Assert;

class RemoveUserIntegration extends TeamworkAssistantTestCase
{
    /**
     * @expectedException \Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException
     */
    function testThatAUserLinkedToAProjectCannotBeRemoved()
    {
        $project = $this->createProject('High-Tech project', 'admin', 'en_US', 'ecommerce', [
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => ['high_tech'],
            ],
        ]);

        $julia = $this->get('pim_user.repository.user')->findOneByIdentifier('julia');
        $this->get('pim_user.remover.user')->remove($julia);
    }

    function testThatAUserNotLinkedToAProjectCanBeRemoved()
    {
        $project = $this->createProject('High-Tech project', 'admin', 'en_US', 'ecommerce', [
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => ['high_tech'],
            ],
        ]);

        $julia = $this->get('pim_user.repository.user')->findOneByIdentifier('julia');
        foreach ($julia->getGroups() as $group) {
            $julia->removeGroup($group);
        }

        $this->get('pim_user.saver.user')->save($julia);
        $this->get('pim_user.remover.user')->remove($julia);

        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        Assert::assertNull($this->get('pim_user.repository.user')->findOneByIdentifier('julia'));
        Assert::assertNull($this->get('pimee_teamwork_assistant.repository.project_status')->findProjectStatus($project, $julia));
    }
}
