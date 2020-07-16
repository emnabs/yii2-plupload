<?php

namespace emhome\plupload;

use Yii;
use yii\web\AssetBundle;

/**
 * PluploadAsset
 *
 * @package plupload\assets
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 * @author emhome <emhome@163.com>
 * @since 2.0
 */
class PluploadAsset extends AssetBundle {

    /**
     * @inheritdoc
     */
    public $publishOptions = [
        'forceCopy' => true,
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'js/plupload.custom.js',
    ];

    /**
     * @inheritdoc
     */
    public $css = [
        'css/plupload.custom.css',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
        'emhome\plupload\PluploadPluginAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init() {
        $this->sourcePath = __DIR__ . '/assets';
        parent::init();
    }

}
