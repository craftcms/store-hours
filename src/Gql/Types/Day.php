<?php

declare(strict_types=1);

namespace CraftCms\StoreHours\Gql\Types;

use CraftCms\Cms\Gql\Types\DateTime;
use CraftCms\Cms\Gql\Types\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Override;

/**
 * A day of a Store Hours field, with a `DateTime` field per time slot.
 *
 * @since 3.1.0
 */
class Day extends ObjectType
{
    #[Override]
    protected function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        return $source[$resolveInfo->fieldName];
    }

    /**
     * Returns day fields prepared for GraphQL object definition, keyed by slot handle (or ID, if it has no handle).
     *
     * @param  array<string, array{name: string, handle: string}>  $slots
     * @return array<string, Type>
     */
    public static function prepareFieldDefinition(array $slots): array
    {
        $contentFields = [];

        foreach ($slots as $slotId => $slot) {
            $contentFields[$slot['handle'] ?: $slotId] = DateTime::getType();
        }

        return $contentFields;
    }
}
