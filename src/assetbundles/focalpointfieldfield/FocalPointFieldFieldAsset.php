<?php
/**
 * Focal Point plugin for Craft CMS 3.x
 *
 * Lorem
 *
 * @link      www.vaersaagod.no
 * @copyright Copyright (c) 2018 Værsågod
 */

namespace vaersaagod\focalpoint\assetbundles\focalpointfieldfield;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Værsågod
 * @package   FocalPoint
 * @since     1.0.0
 */
class FocalPointFieldFieldAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@vaersaagod/focalpoint/assetbundles/focalpointfieldfield/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/FocalPointField.js',
        ];

        $this->css = [
            'css/FocalPointField.css',
        ];

        parent::init();
    }
}
