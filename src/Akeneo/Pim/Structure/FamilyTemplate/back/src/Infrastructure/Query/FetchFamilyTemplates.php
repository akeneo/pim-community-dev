<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\FamilyTemplate\Infrastructure\Query;

use Akeneo\Pim\Structure\FamilyTemplate\Domain\Query\FetchFamilyTemplatesInterface;
use Akeneo\Pim\Structure\FamilyTemplate\Domain\ReadModel\FamilyTemplate;
use GuzzleHttp\Client;

class FetchFamilyTemplates implements FetchFamilyTemplatesInterface
{
    private const TEMPLATES_DIRECTORY = '/templates/families';

    private Client $client;

    public function __construct(
        string $apiUrl,
        string $readToken,
        public string $githubOrgName,
        public string $githubRepoName,
    ) {
        $this->client = new Client([
            'base_uri' => $apiUrl,
            'headers' => [
                'Authorization' => 'Bearer '.$readToken,
                'Accept' => 'application/vnd.github+json',
            ]
        ]);
    }

    public function all(): array
    {
        $response = $this->client->request('GET', '/repos/'.$this->githubOrgName.'/'.$this->githubRepoName.'/zipball');

        $filename = '/tmp/'.uniqid().'_family-templates.zip';
        file_put_contents($filename, $response->getBody()->getContents());

        $archive = new \ZipArchive();
        $archive->open($filename);

        $templates = [];
        for ($i = 0; $i < $archive->numFiles; $i++) {
            $zipFilePath = $archive->getNameIndex($i);
            if ($zipFilePath && $archive->getFromIndex($i) && str_contains($zipFilePath, self::TEMPLATES_DIRECTORY) && str_contains($zipFilePath, '.json')) {
                $pathParts = explode('/', $zipFilePath);
                $templateName = str_replace('.json', '', $pathParts[count($pathParts)-1]);
                $arrayTemplate = json_decode($archive->getFromIndex($i), true);
                $templates[$templateName] = $this->buildReadModel($arrayTemplate);
            }
        }

        unlink($filename);

        return $templates;
    }

    public function byName(string $templateName): FamilyTemplate
    {
        $response = $this->client->request('GET', '/repos/'.$this->githubOrgName.'/'.$this->githubRepoName.'/contents'.self::TEMPLATES_DIRECTORY.'/'.$templateName.'.json');

        $responseContent = json_decode($response->getBody()->getContents(), true);

        $arrayTemplate = json_decode(base64_decode($responseContent['content']), true);

        return $this->buildReadModel($arrayTemplate);
    }

    /**
     * @param array<string, mixed> $arrayTemplate
     */
    private function buildReadModel(array $arrayTemplate): FamilyTemplate
    {
        return new FamilyTemplate(
            $arrayTemplate['templateId'],
            $arrayTemplate['displayName'],
            $arrayTemplate['description'],
            $arrayTemplate['categories'],
            $arrayTemplate['icon'],
            $arrayTemplate['attributes'],
        );
    }
}
