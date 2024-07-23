<?php

namespace Saccas\JsonApiModel;

interface ApiSchemaEnum extends \BackedEnum
{
    public function repositoryClassName(): string;
}
