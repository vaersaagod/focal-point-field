<?php
/**
 * Focal Point Field plugin for Craft CMS 4.x
 *
 * @link      https://www.vaersaagod.no
 * @copyright Copyright (c) 2022 Værsågod
 */

namespace vaersaagod\focalpointfield\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\ThumbableFieldInterface;
use craft\elements\Entry;
use craft\helpers\Cp;
use craft\helpers\Json;
use craft\helpers\Html;
use craft\elements\Asset;

use vaersaagod\focalpointfield\assetbundles\FocalPointFieldAsset;

/**
 * @author    Værsågod
 * @package   Focal Point Field
 * @since     1.0.0
 */
class FocalPointField extends Field implements ThumbableFieldInterface
{

    /** @var string */
    public string $defaultFocalPoint = '50% 50%';

    /** @deprecated in 3.0.0 */
    public int $maxThumbWidth = 300;

    /** @deprecated in 3.0.0 */
    public int $maxThumbHeight = 300;

    /** @var string[] */
    public array $allowedKinds = [Asset::KIND_IMAGE];

    /** @deprecated in 2.0.0 */
    public ?array $defaultPointArray = null;

    /** @inheritdoc */
    public static function displayName(): string
    {
        return 'Focal Point Field';
    }

    public function getIcon(): ?string
    {
        return 'circle-dot';
    }

    /** @inheritdoc */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['defaultFocalPoint'], 'string'];
        $rules[] = [['defaultFocalPoint'], 'default', 'value' => json_encode(['x' => '50', 'y' => '50', 'css' => '50% 50%'], true)];
        $rules[] = [['maxThumbWidth', 'maxThumbHeight'], 'number', 'integerOnly' => true, 'min' => 50];
        return $rules;
    }

    /** @inheritdoc */
    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if (!$value) {
            return ['x' => 50, 'y' => 50, 'css' => '50% 50%'];
        }
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        // Normalize CSS value
        [$left, $top] = explode('%', $value['css'] ?? ['50% 50%']);
        $value['css'] = trim($left) . '% ' . trim($top) . '%';
        return $value;
    }

    /** @inheritdoc */
    public function getSettingsHtml(): ?string
    {
        return
            Cp::textFieldHtml([
                'label' => Craft::t('focal-point-field', 'Default Focal Point'),
                'id' => 'defaultFocalPoint',
                'name' => 'defaultFocalPoint',
                'value' => $this->defaultFocalPoint,
                'errors' => $this->getErrors('defaultFocalPoint'),
            ]);
    }

    /**
     * @param mixed $value
     * @param ElementInterface|null $element
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        $asset = $this->_getAsset($element);
        if ($asset === null || !$this->_validateAssetKind($asset)) {
            return Html::tag('p', Craft::t('focal-point-field', 'This field type can only be used on images, or in nested entries owned by images.'), ['class' => 'error']);
        }

        $asset = clone $asset;

        try {
            $img = $asset->getImg([
                'width' => 400
            ], [
                '1.5x',
                '2x',
                '3x',
            ]);
            $img = Html::modifyTagAttributes($img, [
                'title' => Craft::t('focal-point-field', 'Click image to set focal point'),
                'draggable' => 'false',
                'class' => 'focalpointfield-image',
            ]);

        } catch (\Throwable $e) {
            Craft::error($e->getMessage(), __METHOD__);

            return Html::tag('p', Craft::t('focal-point-field', 'An error occurred when trying to load this image'));
        }

        $view = Craft::$app->getView();

        $namespacedId = $view->namespaceInputId(Html::id($this->handle));

        $jsonVars = Json::encode([
            'name' => $this->handle,
            'namespace' => $namespacedId,
        ]);

        $view->registerAssetBundle(FocalPointFieldAsset::class);
        $view->registerJs("$('#{$namespacedId}-field').FocalPointField(" . $jsonVars . ");");

        $marker = Html::tag(
            'button',
            Html::tag('div', '', [
                'class' => 'focalpointfield-marker-focal-point',
            ]),
            [
                'type' => 'button',
                'class' => 'focalpointfield-marker',
                'hidden' => true,
            ]
        );

        return
            Html::tag('div', $img . $marker, [
                'class' => 'focalpointfield-wrapper',
            ]) .
            Html::hiddenInput($this->handle, json_encode($value, true), [
                'data-focalpointfield-value' => true,
            ]);
    }

    /**
     * @param mixed $value
     * @param ElementInterface $element
     * @param int $size
     * @return string|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getThumbHtml(mixed $value, ElementInterface $element, int $size): ?string
    {
        $asset = $this->_getAsset($element);
        if ($asset === null || !$this->_validateAssetKind($asset)) {
            return null;
        }

        $img = $asset->getThumbHtml($size);
        if (empty($img)) {
            return null;
        }

        Craft::$app->getView()->registerAssetBundle(FocalPointFieldAsset::class);

        $marker = '';
        $value = $this->normalizeValue($value);
        if (is_array($value) && isset($value['x']) && isset($value['y'])) {
            $marker = Html::tag(
                'div',
                Html::tag('div', '', [
                    'class' => 'focalpointfield-marker-focal-point',
                ]),
                [
                    'class' => 'focalpointfield-marker',
                    'style' => "left: {$value['x']}%; top: {$value['y']}%;",
                ]
            );
        }

        return Html::tag('div', $img . $marker, [
            'class' => 'focalpointfield-thumb',
        ]);
    }

    /**
     * @param ElementInterface|null $element
     * @return Asset|null
     * @throws \yii\base\InvalidConfigException
     */
    private function _getAsset(?ElementInterface $element): ?Asset
    {
        $asset = null;
        if ($element instanceof Asset) {
            $asset = $element;
        } else if ($element instanceof Entry) {
            $asset = $element->getOwner();
        }

        if (!$asset instanceof Asset) {
            return null;
        }

        return $asset;
    }

    /**
     * @param Asset $asset
     * @return bool
     */
    private function _validateAssetKind(Asset $asset): bool
    {
        return in_array($asset->kind, $this->allowedKinds, true);
    }
}
