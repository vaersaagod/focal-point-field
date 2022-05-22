<?php
/**
 * Focal Point Field plugin for Craft CMS 4.x
 *
 * @link      https://www.vaersaagod.no
 * @copyright Copyright (c) 2022 Værsågod
 */

namespace vaersaagod\focalpointfield\migrations;

use craft\db\Migration;

use vaersaagod\focalpointfield\fields\FocalPointField;

/**
 * Class Install
 *
 * @author    Værsågod
 * @package   Focal Point Field
 * @since     2.0.0
 *
 */
class Install extends Migration
{

    /**
     * @return false|mixed|void
     */
    public function safeUp()
    {
        // Migrate any fields using the old field type
        $this->update('{{%fields}}', [
            'type' => FocalPointField::class
        ], ['type' => 'vaersaagod\\focalpoint\\fields\\FocalPointField']);
    }

}
