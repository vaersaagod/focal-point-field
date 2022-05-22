<?php
/**
 * Focal Point Field plugin for Craft CMS 4.x
 *
 * @link      https://www.vaersaagod.no
 * @copyright Copyright (c) 2022 Værsågod
 */

namespace vaersaagod\focalpointfield\assetbundles;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Værsågod
 * @package   FocalPoint
 * @since     1.0.0
 */
class FocalPointFieldAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /** @inheritdoc */
    public function init()
    {
        $this->sourcePath = "@vaersaagod/focalpointfield/assetbundles/dist";

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
