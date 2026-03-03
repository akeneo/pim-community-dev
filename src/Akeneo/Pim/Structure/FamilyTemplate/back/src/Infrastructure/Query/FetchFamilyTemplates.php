<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\FamilyTemplate\Infrastructure\Query;

use Akeneo\Pim\Structure\FamilyTemplate\Domain\Query\FetchFamilyTemplatesInterface;
use Akeneo\Pim\Structure\FamilyTemplate\Domain\ReadModel\FamilyTemplate;
use GuzzleHttp\Client;

class FetchFamilyTemplates implements FetchFamilyTemplatesInterface
{
    private const MINIFIED_DIRECTORY = '/dist';
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
        $response = $this->client->request('GET', '/repos/'.$this->githubOrgName.'/'.$this->githubRepoName.'/contents'.self::MINIFIED_DIRECTORY.'/minified.json');

        $content = json_decode($response->getBody()->getContents(), true);
        if (!isset($content['content'])) {
            return [];
        }

        return json_decode(base64_decode($content['content']), true);
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
