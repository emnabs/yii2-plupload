<?php

namespace emhome\plupload;

use Yii;
use yii\web\AssetBundle;

/**
 * Plupload Asset bundle for the Plupload multi-runtime File Uploader script.
 *
 * ```
 * Plupload - multi-runtime File Uploader
 * v2.3.6
 * Released under GPL License.
 * @copyright (c) 2013, Moxiecode Systems AB
 * @license http://www.plupload.com/license
 * @see http://www.plupload.com/contributing
 * ```
 * 
 * @author emhome <emhome@163.com>
 * @since 2.0.1
 */
class PluploadPluginAsset extends AssetBundle {

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/plupload';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/plupload.full.min.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
    ];

    /**
     * @var string 使用语言包
     */
    public $language;

    /**
     * @inheritdoc
     */
    public function init() {
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
