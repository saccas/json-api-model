<?php

namespace Saccas\JsonApiModel;

use Illuminate\Support\Collection;
use Swis\JsonApi\Client\Interfaces\DocumentInterface;
use Swis\JsonApi\Client\Interfaces\ItemInterface;

class JsonApiModel
{
    public function __construct(
        protected JsonApiContext $repositoryManager,
        protected DocumentInterface $document,
        protected ItemInterface $dataItem,
    ) {
    }

    protected function getAttribute(string $attribute): mixed
    {
        return $attribute === 'id' ? $this->dataItem->getId() : $this->dataItem->getAttribute($attribute);
    }

    protected function getDateAttribute(string $attribute): ?\DateTime
    {
        $value = $this->getAttribute($attribute);
        if (! isset($value)) {
            return null;
        }
        return new \DateTime($value);
    }

    protected function getRelationSingle(string $relationShipName, string $modelClassName): ?JsonApiModel
    {
        $relation = $this->dataItem->getRelations()[$relationShipName];
        if (!$relation->hasAssociated()) {
            $self = $this->fetchSelfWithInclude($relationShipName);
            return $self->getRelationSingle($relationShipName, $modelClassName);
        }

        $item = $relation->getAssociated();
        if (!isset($item)) {
            return null;
        }

        return new $modelClassName($this->repositoryManager, $this->document, $item);
    }

    protected function getRelationMultiple(string $relationShipName, string $modelClassName): Collection
    {
        $relation = $this->dataItem->getRelations()[$relationShipName];
        if (!$relation->hasAssociated()) {
            $self = $this->fetchSelfWithInclude($relationShipName);
            return $self->getRelationMultiple($relationShipName, $modelClassName);
        }

        $models = [];
        foreach ($relation->getAssociated() as $dataItem) {
            $models[] = new $modelClassName($this->repositoryManager, $this->document, $dataItem);
        }

        return new Collection($models);
    }

    protected function fetchSelfWithInclude(string $relationShipName): JsonApiModel
    {
        $selfRepository = $this->repositoryManager->getRepository($this->dataItem->getType());
        $self = $selfRepository->getOne($this->dataItem->getId(), ['include' => $relationShipName]);
        return $self;
    }
}