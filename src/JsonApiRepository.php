<?php

namespace Saccas\JsonApiModel;

use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;

/**
 * @template T of object
 */
class JsonApiRepository
{
    protected string $endpoint;
    protected string $modelClass;

    public function __construct(
        protected JsonApiContext $repositoryManager,
    ) {
    }

    /**
     * @return Enumerable<T>
     */
    public function getAll(array $parameters = [], array $headers = []): Enumerable
    {
        $nextUrl = $this->endpoint . '?' . http_build_query($parameters);
        $generator = function () use ($nextUrl, $headers) {
            while (isset($nextUrl)) {
                $document = $this->repositoryManager->getDocumentClient()->get($nextUrl, $headers);
                foreach ($document->getData() as $dataItem) {
                    yield new $this->modelClass($this->repositoryManager, $document, $dataItem);
                }
                $nextUrl = ($document->getLinks()['next'] ?? null)?->getHref();
            }
        };

        return new LazyCollection($generator);
    }

    public function getOne(string $id, array $parameters = [], array $headers = []): JsonApiModel
    {
        $url = $this->endpoint . '/' . urlencode($id) . '?' . http_build_query($parameters);
        $document = $this->repositoryManager->getDocumentClient()->get($url, $headers);
        return new $this->modelClass($this->repositoryManager, $document, $document->getData());
    }
}
