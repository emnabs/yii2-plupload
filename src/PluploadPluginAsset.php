<?php

namespace emhome\plupload;

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
class PluploadPluginAsset extends AssetBundle
{

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

}
