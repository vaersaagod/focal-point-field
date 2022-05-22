<?php
/**
 * Focal Point Field plugin for Craft CMS 4.x
 *
 * @link      https://www.vaersaagod.no
 * @copyright Copyright (c) 2022 Værsågod
 */

namespace vaersaagod\focalpointfield;

use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;

use vaersaagod\focalpointfield\fields\FocalPointField;

use yii\base\Event;

/**
 * Class Plugin
 *
 * @author    Værsågod
 * @package   Focal Point Field
 * @since     1.0.0
 *
 */
class Plugin extends \craft\base\Plugin
{

    /** @inheritdoc */
    public string $schemaVersion = '1.0.0';

    /** @inheritdoc */
    public function init()
    {
        parent::init();

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            static function (RegisterComponentTypesEvent $event) {
                $event->types[] = FocalPointField::class;
            }
        );
    }

}
