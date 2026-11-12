<?php

declare(strict_types=1);

namespace CraftCms\StoreHours\Gql\Types\Generators;

use CraftCms\Cms\Gql\Contracts\GeneratorInterface;
use CraftCms\Cms\Gql\Contracts\SingleGeneratorInterface;
use CraftCms\Cms\Gql\GqlEntityRegistry;
use CraftCms\Cms\Gql\Types\ObjectType;
use CraftCms\Cms\Support\Facades\Gql;
use CraftCms\StoreHours\Fields\StoreHours;
use CraftCms\StoreHours\Gql\Types\Day;

/**
 * Generates the `<fieldHandle>_Day` GraphQL type for a Store Hours field.
 *
 * @since 3.1.0
 */
class DayType implements GeneratorInterface, SingleGeneratorInterface
{
    public static function generateTypes(mixed $context = null): array
    {
        return [static::generateType($context)];
    }

    public static function getName(mixed $context = null): string
    {
        /** @var StoreHours $context */
        return "{$context->handle}_Day";
    }

    public static function generateType(mixed $context): ObjectType
    {
        /** @var StoreHours $context */
        $typeName = self::getName($context);

        return GqlEntityRegistry::getOrCreate($typeName, fn () => new Day([
            'name' => $typeName,
            'fields' => fn () => Gql::prepareFieldDefinitions(Day::prepareFieldDefinition($context->slots), $typeName),
        ]));
    }
}
