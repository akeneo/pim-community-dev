<?php

namespace Pim\Behat\Context\Storage;

use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;

class FileInfoStorage extends PimContext
{
    /**
     * Checks that a file (or media) exists in database
     *
     * @param string $originalFilename
     *
     * @Then /^The file with original filename "([^"]*)" should exists in database$/
     */
    public function theFileShouldExistInDatabase($originalFilename)
    {
        $fileInfoRepoClass  = $this->getParameter('akeneo_file_storage.model.file_info.class');
        $fileInfoRepository = $this->getRepository($fileInfoRepoClass);

        $fileInfo = $fileInfoRepository->findOneBy(['originalFilename' => $originalFilename]);

        Assert::assertNotNull($fileInfo, sprintf(
            'Unable to find file with original filename "%s" in database',
            $originalFilename
        ));
    }

    /**
     * @param string $entityClass
     *
     * @return FileInfoRepositoryInterface
     */
    private function getRepository($entityClass)
    {
        return $this->getMainContext()->getEntityManager()->getRepository($entityClass);
    }
}
