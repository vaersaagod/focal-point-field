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
use craft\helpers\Cp;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\helpers\Html;
use craft\elements\Asset;

use vaersaagod\focalpointfield\FocalPoint;
use vaersaagod\focalpointfield\assetbundles\FocalPointFieldAsset;

use yii\db\Schema;

/**
 * @author    Værsågod
 * @package   Focal Point Field
 * @since     1.0.0
 */
class FocalPointField extends Field
{

    /** @var string */
    public string $defaultFocalPoint = '50% 50%';

    /** @var int */
    public int $maxThumbWidth = 300;

    /** @var int */
    public int $maxThumbHeight = 300;

    /** @var string[] */
    public array $allowedKinds = [Asset::KIND_IMAGE];

    /** @var array|null */
    /** @deprecated in 2.0.0 */
    public ?array $defaultPointArray = null;

    /** @inheritdoc */
    public static function displayName(): string
    {
        return 'Focal Point Field';
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
    public function getContentColumnType(): string
    {
        return Schema::TYPE_STRING;
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
            ]) .
            Cp::textFieldHtml([
                'label' => Craft::t('focal-point-field', 'Max Thumb Width'),
                'id' => 'maxThumbWidth',
                'name' => 'maxThumbWidth',
                'type' => 'number',
                'size' => 5,
                'min' => '50',
                'step' => '10',
                'value' => $this->maxThumbWidth,
                'errors' => $this->getErrors('maxThumbWidth'),
            ]) .
            Cp::textFieldHtml([
                'label' => Craft::t('focal-point-field', 'Max Thumb Height'),
                'id' => 'maxThumbHeight',
                'name' => 'maxThumbHeight',
                'type' => 'number',
                'size' => 5,
                'min' => '50',
                'step' => '10',
                'value' => $this->maxThumbHeight,
                'errors' => $this->getErrors('maxThumbHeight'),
            ]);
        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'focal-point-field/settings.twig',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * @param mixed $value
     * @param ElementInterface|null $element
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {

        /** @var Asset|null $asset */
        $asset = null;
        if ($element instanceof Asset) {
            $asset = $element;
        } else if ($element && isset($element->owner) && $element->owner instanceof Asset) {
            $asset = $element->owner;
        }

        if (!$asset || !in_array($asset->kind, $this->allowedKinds)) {
            return Html::tag('p', Craft::t('focal-point-field', 'This field type can only be used on images'), ['class' => 'error']);
        }

        try {

            $asset->setTransform([
                'width' => $this->maxThumbWidth * 2,
                'height' => $this->maxThumbWidth * 2,
                'mode' => 'fit',
            ]);

            $width = round($asset->getWidth() / 2);
            $height = round($width * ($asset->getWidth() / $asset->getHeight()));

            $img = Html::img($asset->getUrl(), [
                'width' => $width,
                'height' => $height,
                'title' => Craft::t('focal-point-field', 'Click image to set focal point'),
                'draggable' => 'false',
                'class' => 'focalpointfield-thumb',
            ]);

        } catch (\Throwable $e) {
            Craft::error($e->getMessage(), __METHOD__);

            return Html::p('p', Craft::t('focal-point-field', 'An error occurred when trying to load this image'));
        }

        $view = Craft::$app->getView();

        $namespacedId = $view->namespaceInputId(Html::id($this->handle));

        $jsonVars = Json::encode([
            'name' => $this->handle,
            'namespace' => $namespacedId,
        ]);

        $view->registerAssetBundle(FocalPointFieldAsset::class);
        $view->registerJs("$('#{$namespacedId}-field').FocalPointField(" . $jsonVars . ");");

        return
            Html::tag('div', $img, [
                'class' => 'focalpointfield-wrapper',
                'style' => [
                    'width' => "{$width}px",
                    'max-width' => '100%',
                ],
            ]) .
            Html::hiddenInput($this->handle, json_encode($value, true), [
                'data-focalpointfield-value' => true,
            ]);
    }
}
