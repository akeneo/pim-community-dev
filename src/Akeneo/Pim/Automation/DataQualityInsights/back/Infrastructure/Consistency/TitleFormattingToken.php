<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

class TitleFormattingToken
{
    /** @var string */
    private $jwtVerifyingKey;

    /** @var string */
    private $akeneoPimUrl;

    /** @var string */
    private $kernelProjectDir;

    /** @var string */
    private $papoProjectCodeTruncated;

    /** @var string */
    private $papoProjectCodeHashed;

    public function __construct(string $jwtVerifyingKey, string $akeneoPimUrl, string $kernelProjectDir, string $papoProjectCodeTruncated, string $papoProjectCodeHashed)
    {
        $this->jwtVerifyingKey = $jwtVerifyingKey;
        $this->akeneoPimUrl = $akeneoPimUrl;
        $this->kernelProjectDir = $kernelProjectDir;
        $this->papoProjectCodeTruncated = $papoProjectCodeTruncated;
        $this->papoProjectCodeHashed = $papoProjectCodeHashed;
    }

    public function getTokenAsString(): string
    {
        $signer = new Sha256();
        $key = new Key($this->jwtVerifyingKey);

        $token = (new Builder())->withClaim('customer_ids', $this->getCustomerIds())
            ->getToken($signer, $key);

        return $token->__toString();
    }

    private function getCustomerIds(): array
    {
        return [
            'akeneo_pim_url' => $this->akeneoPimUrl,
            'vcs' => $this->extractAkeneoVcs(),
            'papo_project_code_truncated' => $this->papoProjectCodeTruncated,
            'papo_project_code_hashed' => $this->papoProjectCodeHashed,
        ];
    }

    private function extractAkeneoVcs(): array
    {
        $akeneoVcs = [];

        $composerJsonFile = rtrim($this->kernelProjectDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'composer.json';

        if (false === is_file($composerJsonFile)) {
            return $akeneoVcs;
        }

        $composerJsonContent = json_decode(file_get_contents($composerJsonFile), true);

        if (!array_key_exists('repositories', $composerJsonContent) || empty($composerJsonContent['repositories'])) {
            return $akeneoVcs;
        }

        $repositories = $composerJsonContent['repositories'];

        $akeneoVcs = array_filter(
            $repositories,
            function ($vcs) {
                return (!empty($vcs['url']) && false !== strpos($vcs['url'], 'distribution.akeneo.com'));
            }
        );

        $akeneoVcs = array_map(
            function ($vcs) {
                return $vcs['url'];
            },
            $akeneoVcs
        );

        return $akeneoVcs;
    }
}
