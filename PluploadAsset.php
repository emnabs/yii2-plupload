<?php

/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace emhome\plupload;

use Yii;
use yii\web\AssetBundle;

/**
 * Class PluploadAsset
 * @package xutl\plupload
 */
class PluploadAsset extends AssetBundle {

    public $publishOptions = [
        'forceCopy' => true//YII_DEBUG
    ];
    public $sourcePath = '@widgets/plupload/assets';

    /**
     * @var array 包含的JS
     */
    public $js = [
        'js/plupload.full.min.js',
        'js/custom.js',
    ];

    /**
     * @var array 包含的CSS
     */
    public $css = [
        'css/plupload.css',
    ];

    /**
     * @var array 定义依赖
     */
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    public $language;

    /**
     * @inheritdoc
     */
    public function init() {
        // 设置加载语言包
        if (!$this->language) {
            $this->language = Yii::$app->language;
        }
        $language = str_replace('-', '_', $this->language);
        $fallbackLanguage = substr($this->language, 0, 2);
        if ($fallbackLanguage !== $this->language && !file_exists(Yii::getAlias($this->sourcePath . "/js/i18n/{$language}.js"))) {
            $language = $fallbackLanguage;
        }
        $this->js[] = "js/i18n/$language.js";
        parent::init();
    }

}
