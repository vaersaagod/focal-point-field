<?php
/**
 * Focal Point plugin for Craft CMS 3.x
 *
 * Lorem
 *
 * @link      www.vaersaagod.no
 * @copyright Copyright (c) 2018 Værsågod
 */

namespace vaersaagod\focalpoint;

use vaersaagod\focalpoint\fields\FocalPointField as FocalPointFieldField;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;

use yii\base\Event;

/**
 * Class FocalPoint
 *
 * @author    Værsågod
 * @package   FocalPoint
 * @since     1.0.0
 *
 */
class FocalPoint extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var FocalPoint
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = FocalPointFieldField::class;
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'focal-point',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
