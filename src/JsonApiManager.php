<?php

namespace Saccas\JsonApiModel;

use Psr\Http\Client\ClientInterface;

abstract class JsonApiManager
{
    protected JsonApiContext $context;

    /** @var array<string, class-string<JsonApiRepository> */
    protected array $schemaRepositoryClassMap;

    public function __construct(
        ClientInterface $httpClient,
        string $baseUri,
        array $defaultHeaders = []
    ) {
        $typeMapper = new \Swis\JsonApi\Client\TypeMapper();
        $apiClient = new \Swis\JsonApi\Client\Client($httpClient);
        $apiClient->setBaseUri($baseUri);
        $apiClient->setDefaultHeaders($defaultHeaders);

        $responseParser = \Swis\JsonApi\Client\Parsers\ResponseParser::create($typeMapper);
        $documentClient = new \Swis\JsonApi\Client\DocumentClient($apiClient, $responseParser);
        $this->context = new \Saccas\JsonApiModel\JsonApiContext($documentClient);

        foreach ($this->schemaRepositoryClassMap as $apiSchema => $repositoryClassName) {
            $repository = new $repositoryClassName($this->context);
            $this->context->addRepository($apiSchema, $repository);
        }
    }

    public function getRepository(string $apiSchema): JsonApiRepository
    {
        return $this->context->getRepository($apiSchema);
    }
}
