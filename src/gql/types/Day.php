<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\storehours\gql\types;

use craft\gql\base\ObjectType;
use craft\gql\types\DateTime;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class Day
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.1.0
 */
class Day extends ObjectType
{
    /**
     * @inheritdoc
     */
    protected function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        return $source[$resolveInfo->fieldName];
    }

    /**
     * Returns day fields prepared for GraphQL object definition.
     *
     * @param array $slots
     * @return array
     */
    public static function prepareFieldDefinition(array $slots): array
    {
        $contentFields = [];
        foreach ($slots as $slotId => $slot) {
            $handle = ($slot['handle'] ?? false) ?: $slotId;
            $contentFields[$handle] = DateTime::getType();
        }
        return $contentFields;
    }
}
