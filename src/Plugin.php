<?php
namespace craft\storehours;

use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use craft\storehours\fields\StoreHoursField;
use yii\base\Event;


/**
 * Store Hours plugin
 */
class Plugin extends \craft\base\Plugin
{
    public function init()
    {
        parent::init();

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = StoreHoursField::class;
        });
    }

}
