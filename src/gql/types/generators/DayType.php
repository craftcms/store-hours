<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\storehours\gql\types\generators;

use Craft;
use craft\gql\base\GeneratorInterface;
use craft\gql\base\ObjectType;
use craft\gql\base\SingleGeneratorInterface;
use craft\gql\GqlEntityRegistry;
use craft\storehours\Field;
use craft\storehours\gql\types\Day;

/**
 * Class DayType
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.1.0
 */
class DayType implements GeneratorInterface, SingleGeneratorInterface
{
    /**
     * @inheritdoc
     */
    public static function generateTypes(mixed $context = null): array
    {
        return [static::generateType($context)];
    }

    /**
     * Returns the generator name.
     *
     * @return string
     */
    public static function getName($context = null): string
    {
        /** @var Field $context */
        return "{$context->handle}_Day";
    }

    /**
     * @inheritdoc
     */
    public static function generateType(mixed $context): ObjectType
    {
        $typeName = self::getName($context);

        return GqlEntityRegistry::getOrCreate($typeName, fn() => new Day([
            'name' => $typeName,
            'fields' => function() use ($context, $typeName) {
                /** @var Field $context */
                $contentFields = Day::prepareFieldDefinition($context->slots);
                return Craft::$app->getGql()->prepareFieldDefinitions($contentFields, $typeName);
            },
        ]));
    }
}
