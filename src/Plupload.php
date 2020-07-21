<?php

namespace emhome\plupload;

use Yii;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\widgets\InputWidget;

/**
 * Plupload
 *
 * @author emhome <emhome@163.com>
 * @since 2.0.1
 */
class Plupload extends InputWidget {

    const SIZE_UNIT = 'kb';

    public $responeElement;
    public $htmlOptions = ['class' => 'plupload_wrapper'];

    /**
     * @var string 上传地址
     */
    public $url;
    public $wrapperOptions;
    public $attachUrl;
    //
    public $browseIcon = 'ionicons ion-android-add';
    public $browseLabel = '上传图片';
    public $browseOptions;
    //
    public $uploadLabel = 'Upload Files';
    public $uploadOptions = [];
    //
    public $errorContainer;
    public $errorImageUrl = 'error.png';
    //
    public $previewContainer;
    public $previewOptions = ['class' => 'plupload_preview'];
    public $containerOptions = ['class' => 'plupload_container'];
    //
    public $options = [];
    public $autoUpload = true;
    public $showUploadProgress = true;
    public $chunk_size = 0;
    public $events = [];
    public $multiSelection = false;
    public $showUploadFiles = [];
    public $customOptions = [];
    public $resize = [];
    private $resizeOptions = [
        'crop' => true,
        'quality' => 100,
    ];
    public $allow_max_nums = 0;

    /**
     * @var string 上传模块模版
     * default,single,mutil,mobile
     */
    public $template = 'default';

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
        // Make sure URL is provided
        if (!$this->url) {
            throw new Exception(Yii::t('yii', '{class} must specify "url" property value.', [
                '{class}' => get_class($this),
            ]));
        }
        // Set id of this widget
        if (!isset($this->htmlOptions['id'])) {
            $this->htmlOptions['id'] = $this->getId();
        }
        $id = $this->htmlOptions['id'];
        // Set respone element of this widget.
        if ($this->hasModel()) {
            if (!preg_match(Html::$attributeRegex, $this->attribute, $matches)) {
                throw new InvalidParamException('Attribute name must contain word characters only.');
            }
            $this->attribute = $matches[2];
            $model = $this->model;
            $attribute = $this->attribute;
            $this->name = Html::getInputName($this->model, $this->attribute);
            $value = $model->$attribute;
            if (is_array($value) || $matches[3] === '[]') {
                $this->multiSelection = true;
            }
            if ($value && $this->multiSelection && !is_array($value)) {
                $model->$attribute = [$value];
            }
            $this->responeElement = Html::getInputId($this->model, $this->attribute);
            $this->options['input_name'] = $this->name;
        } else {
            if (!$this->responeElement) {
                $this->responeElement = $id . "_input";
            }
        }
        // 设置选取按钮
        if (!isset($this->browseOptions['id'])) {
            $this->browseOptions['id'] = $id . "_browse";
        }
        if (!isset($this->browseOptions['class'])) {
            $this->browseOptions['class'] = "plupload-btn-browse";
        }
        if (!empty($this->resize)) {
            $this->options['multipart_params']['resize'] = ArrayHelper::merge($this->resizeOptions, $this->resize);
        }
        if ($this->errorImageUrl !== false && !Url::isRelative($this->errorImageUrl)) {
            $this->options['error_image_url'] = $this->errorImageUrl;
        }
        if ($this->multiSelection) {
            Html::addCssClass($this->browseOptions, 'btn btn-success');
            Html::addCssClass($this->htmlOptions, 'plupload_many_thumb');
        } else {
            Html::addCssClass($this->htmlOptions, 'plupload_one');
            $this->setWrapperStyle();
            $this->allow_max_nums = 1;
        }
        // 预览
        if (!isset($this->previewOptions['id'])) {
            $this->previewOptions['id'] = $id . "_preview";
        }
        $this->previewContainer = $id . "_preview";
        // 设置选取按钮
        if (!isset($this->containerOptions['id'])) {
            $this->containerOptions['id'] = $id . "_container";
        }
        if (!$this->autoUpload) {
            if (!isset($this->uploadOptions['id'])) {
                $this->uploadOptions['id'] = $id . "_upload";
            }
            if (!isset($this->uploadOptions['class'])) {
                $this->uploadOptions['class'] = "plupload-btn-upload";
            }
        }
        if (!isset($this->errorContainer)) {
            $this->errorContainer = $id . "_error";
        }
        if (!isset($this->options['multipart_params'])) {
            $this->options['multipart_params'] = [];
        }
        $this->options['multipart_params'][Yii::$app->request->csrfParam] = Yii::$app->request->csrfToken;
        if ($this->allow_max_nums) {
            $this->options['multipart_params']['max_file_nums'] = $this->allow_max_nums;
        }
        $this->registerAssets();
    }

    /**
     * @inheritdoc
     */
    public function run() {
        parent::run();
        echo $this->renderPlupload();
    }

    /**
     * Renders the date picker widget.
     */
    protected function renderPlupload() {
        $options = [
            'allow_max_nums' => $this->allow_max_nums,
            'containerOptions' => $this->containerOptions,
            'previewOptions' => $this->previewOptions,
            'errorContainer' => $this->errorContainer,
            'browseLabel' => $this->browseLabel,
            'browseOptions' => $this->browseOptions,
            'autoUpload' => $this->autoUpload,
            'uploadLabel' => $this->uploadLabel,
            'uploadOptions' => $this->uploadOptions,
            'htmlOptions' => $this->htmlOptions,
            'attachUrl' => $this->attachUrl,
            'multiSelection' => $this->multiSelection,
        ];
        if ($this->hasModel()) {
            $options['model'] = $this->model;
            $options['attribute'] = $this->attribute;
        }
        return $this->render($this->template, $options);
    }

    /**
     * Registers the needed assets
     */
    public function registerAssets() {
        $bundle = $this->registerAssetBundle();
        $view = $this->getView();
        $defaultOptions = [
            'runtimes' => 'html5,flash,silverlight,html4',
            'container' => $this->containerOptions['id'],
            'browse_button' => $this->browseOptions['id'],
            'url' => Url::to($this->url),
            'max_file_size' => $this->getUploadMaxSize(),
            'chunk_size' => $this->getChunkSize(),
            'error_container' => "#{$this->errorContainer}",
            'multi_selection' => $this->multiSelection,
            'flash_swf_url' => $bundle->baseUrl . "/Moxie.swf",
            'silverlight_xap_url' => $bundle->baseUrl . "/Moxie.xap",
            'views' => [
                'list' => true,
                'thumbs' => true,
                'active' => 'thumbs',
            ],
            'filters' => [
                'mime_types' => [
                    [
                        'title' => "Image files",
                        'extensions' => "jpg,gif,png",
                    ],
                ],
            ],
        ];

        $options = Json::encode(ArrayHelper::merge($defaultOptions, $this->options));

        $scripts = implode("\n", [
            "var {$this->id} = new plupload.Uploader({$options});",
            "{$this->id}.init();",
            "{$this->buildCallbackEvent()}",
        ]);
        $view->registerJs($scripts);
    }

    /**
     * Registers the asset bundle and locale
     */
    public function registerAssetBundle() {
        return PluploadAsset::register($this->view);
    }

    /**
     * Registers the asset bundle and locale
     */
    protected function buildCallbackEvent() {
        $events = ArrayHelper::merge(self::buildEvents(), $this->events);
        if (empty($events)) {
            return;
        }
        $script = '';
        foreach ($events as $event => $callback) {
            $script .= $this->id . ".bind('$event', $callback);\n";
        }
        return $script;
    }

    /**
     * buildEvents
     * Generate event JavaScript
     */
    protected function buildEvents() {
        $registerEvents = [
            'Init',
            'PostInit',
            'FilesAdded',
            'FilesRemoved',
            'BeforeUpload',
            'FileUploaded',
            'UploadComplete',
            'Refresh',
            'Error',
        ];
        //是否显示上传进度
        if ($this->showUploadProgress) {
            $registerEvents[] = 'UploadProgress';
        }
        //register script of plupload evnets
        $configs = [
            'errorContainer' => $this->errorContainer,
            'previewContainer' => $this->previewContainer,
            'uploadOptions' => $this->uploadOptions,
            'multiSelection' => $this->multiSelection,
            'autoUpload' => $this->autoUpload,
            'responeElement' => $this->responeElement,
            'attachUrl' => $this->attachUrl,
        ];
        $event = new PluploadEvents($configs);
        return $event->getScripts($registerEvents);
    }

    /**
     * 设置样式
     */
    protected function setWrapperStyle() {
        if (isset($this->htmlOptions['style'])) {
            return;
        }
        $width = 480;
        $height = 300;
        if (isset($this->options['resize']) && !empty($resize = $this->options['resize'])) {
            $width = !isset($resize['width']) ?: $resize['width'];
            $height = !isset($resize['height']) ?: $resize['height'];
        }
        if (isset($this->wrapperOptions['width'])) {
            $width = (int) $this->wrapperOptions['width'];
        }
        if (isset($this->wrapperOptions['height'])) {
            $height = (int) $this->wrapperOptions['height'];
        }
        $this->htmlOptions['style'] = "width: {$width}px; height: {$height}px;";
    }

    /**
     * @return int the max upload size in MB
     */
    protected function getUploadMaxSize() {
        $upload_max_filesize = (int) (ini_get('upload_max_filesize'));
        $post_max_size = (int) (ini_get('post_max_size'));
        $memory_limit = (int) (ini_get('memory_limit'));
        return min($upload_max_filesize, $post_max_size, $memory_limit) . 'mb';
    }

    /**
     * 分块大小
     */
    protected function getChunkSize() {
        $chunksize = (int) $this->chunk_size;
        if ($chunksize) {
            return $chunksize . self::SIZE_UNIT;
        }
        return $chunksize;
    }

}
