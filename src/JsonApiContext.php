<?php

namespace Saccas\JsonApiModel;

use Swis\JsonApi\Client\Interfaces\DocumentClientInterface;

class JsonApiContext
{
    protected array $repositories = [];

    public function __construct(
        protected DocumentClientInterface $documentClient,
    ) {
    }

    public function getDocumentClient(): DocumentClientInterface
    {
        return $this->documentClient;
    }

    public function addRepository(string $apiType, JsonApiRepository $repository)
    {
        $this->repositories[$apiType] = $repository;
    }

    public function getRepository(string $apiType): JsonApiRepository
    {
        return $this->repositories[$apiType];
    }
}