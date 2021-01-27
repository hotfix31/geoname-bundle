<?php

namespace Hotfix\Bundle\GeoNameBundle\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Downloader
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function download(string $url, string $saveAs, ?callable $progress = null): void
    {
        $options = [];
        if ($progress && is_callable($progress)) {
            $options['on_progress'] = static function (int $dlNow, int $dlSize, array $info) use ($progress) {
                $dlSize && $progress($dlNow / $dlSize);
            };
        }

        $request = $this->httpClient->request('GET', $url, $options);
        foreach ($this->httpClient->stream($request) as $chunk => $response) {
            if (!$response->isLast()) {
                continue;
            }

            file_put_contents($saveAs, $chunk->getContent());
        }
    }
}