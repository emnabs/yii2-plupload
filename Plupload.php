<?php

namespace emhome\plupload;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\widgets\InputWidget;
use yii\base\InvalidParamException;

class Plupload extends InputWidget {

    public $responeElementId;
    public $htmlOptions = ['class' => 'plupload_wrapper'];
    //上传地址
    public $url;
    public $wrapperOptions;
    //
    public $browseIcon = 'ionicons ion-android-add';
    public $browseLabel = '<i class="ionicons ion-ios-plus-empty"></i>';
    public $browseOptions;
    //
    public $uploadLabel = 'Upload Files';
    public $uploadOptions = [];
    //
    public $errorContainer;
    //
    public $previewContainer;
    public $previewOptions = ['class' => 'plupload_preview'];
    public $containerOptions = ['class' => 'plupload_container'];
    //
    public $options = [];
    public $autoUpload = false;
    public $showUploadProgress = true;
    public $chunk_size = 1;
    public $events = [];
    public $multiSelection = false;
    public $showUploadFiles = [];
    public $customOptions = [];
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
        if (empty($this->url)) {
            throw new Exception(Yii::t('yii', '{class} must specify "url" property value.', [
                '{class}' => get_class($this)
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

            if ($this->multiSelection && !is_array($value)) {
                $model->$attribute = [$value];
            }

            $this->responeElementId = Html::getInputId($this->model, $this->attribute);
        } else {
            if (!$this->responeElementId) {
                $this->responeElementId = $id . "_input";
            }
        }

        // 设置选取按钮
        if (!isset($this->browseOptions['id'])) {
            $this->browseOptions['id'] = $id . "_browse";
        }
        if (!isset($this->browseOptions['class'])) {
            $this->browseOptions['class'] = "plupload-btn-browse";
        }

        if ($this->multiSelection) {
            Html::addCssClass($this->browseOptions, 'btn btn-success');
            Html::addCssClass($this->htmlOptions, 'plupload_many_thumb');
        } else {
            Html::addCssClass($this->htmlOptions, 'plupload_one');
            // Set wrapper of this widget style.
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
        //echo $this->renderInput();
        echo $this->renderPlupload();
    }

    /**
     * Renders the date picker widget.
     */
    protected function renderPlupload() {
        $options = [
            'multi' => $this->multiSelection,
            'allow_max_nums' => $this->allow_max_nums,
            'responeElementId' => $this->responeElementId,
            'data' => $this->getInputValue('array'),
            'containerOptions' => $this->containerOptions,
            'previewOptions' => $this->previewOptions,
            'errorContainer' => $this->errorContainer,
            'browseLabel' => $this->browseLabel,
            'browseOptions' => $this->browseOptions,
            'autoUpload' => $this->autoUpload,
            'uploadLabel' => $this->uploadLabel,
            'uploadOptions' => $this->uploadOptions,
            'showUploadProgress' => $this->showUploadProgress
        ];

        if ($this->hasModel()) {
            $options['model'] = $this->model;
            $options['attribute'] = $this->attribute;
        }

        $content = $this->render($this->template, $options);
        return Html::tag('div', $content, $this->htmlOptions);
    }

    /**
     * Renders the source input for the DatePicker plugin.
     *
     * @return string
     */
    protected function renderInput() {
        if ($this->multiSelection) {
            return;
        }
        return Html::hiddenInput($this->name, $this->value, [
            'id' => $this->responeElementId,
        ]);
        if ($this->hasModel()) {
            return Html::activeHiddenInput($this->model, $this->attribute);
        } else {
            return Html::hiddenInput($this->name, $this->value, $this->options);
        }
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
            'max_file_size' => self::getPHPMaxUploadSize() . 'mb',
            'chunk_size' => $this->getChunkSize(),
            'error_container' => "#{$this->errorContainer}",
            'multi_selection' => $this->multiSelection,
            'flash_swf_url' => $bundle->baseUrl . "/Moxie.swf",
            'silverlight_xap_url' => $bundle->baseUrl . "/Moxie.xap",
            'views' => [
                'list' => true,
                'thumbs' => true,
                'active' => 'thumbs'
            ]
        ];

        $options = ArrayHelper::merge($defaultOptions, $this->options);
        $options = Json::encode($options);

        $scripts = implode("\n", [
            "var {$this->id} = new plupload.Uploader({$options});",
            "{$this->id}.init();",
            "{$this->buildCallbackEvent()}",
        ]);
        $customOptions = '';
        if ($this->multiSelection) {
            $customOptions = Json::encode([
                'name' => $this->name,
                'id' => $this->responeElementId,
            ]);
        }
        $scripts .= "\nCustom.init({$customOptions});";

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
     * 设置样式
     */
    protected function setWrapperStyle() {
        if (isset($this->htmlOptions['style'])) {
            return;
        }
        $width = isset($this->wrapperOptions['width']) ? (int) $this->wrapperOptions['width'] : 360;
        $height = isset($this->wrapperOptions['height']) ? (int) $this->wrapperOptions['height'] : 180;
        if ($width) {
            $width = sprintf("width: %dpx;", $width);
        }
        if ($height) {
            $height = sprintf("height: %dpx;", $height);
        }
        $this->htmlOptions['style'] = $width . $height;
    }

    /**
     * 分块大小
     */
    protected function getChunkSize() {
        $chunksize = (int) $this->chunk_size;
        if ($chunksize) {
            return $chunksize . 'kb';
        }
        return $chunksize;
    }

    /**
     * @return int the max upload size in MB
     */
    protected function buildEvents() {
        // Generate event JavaScript
        $defaultEvents = [];

        //Error
        $defaultEvents['Error'] = 'function(uploader, error){
			var errorElement = jQuery("#' . $this->errorContainer . '");
			errorElement.html("Error #:"+error.code+" "+error.message).show();
		}';

        //UploadProgress
        if ($this->showUploadProgress) {
            $defaultEvents['UploadProgress'] = 'function(uploader, file){
                $("#"+file.id).find(".plupload_file_percent").html(file.percent + "%");
				$("#"+file.id).find(".progress-bar").width(file.percent + "%");
			}';
        }

        $appendHtmlType = 'html';
        $responeElement = '';
        if ($this->multiSelection) {
            $appendHtmlType = 'prepend';
        }

        $defaultEvents['Init'] = 'function(uploader){
            var uploaded_nums = jQuery("#' . $this->previewContainer . '").length;
            var params = uploader.getOption("multipart_params");
            
            if(uploaded_nums >= params.max_file_nums){
                jQuery("#"+uploader.settings.container).hide();
            }
		}';


        //开启自动上传
        $activeUploadResponse = '';
        if ($this->autoUpload) {
            $defaultEvents['FilesAdded'] = 'function(uploader, files){
				jQuery("#' . $this->errorContainer . '").hide();

				var upfiles = "";
				plupload.each(files, function(file) {
					upfiles += Custom.tplUploadItem(file);
				});

                $(document).on("click", ".plupload_file_action", function () {
                    var id = $(this).parent().attr("id");
                    uploader.removeFile(id);
                });
                
				jQuery("#' . $this->previewContainer . '").' . $appendHtmlType . '(upfiles);
                uploader.refresh();
				uploader.disableBrowse(true);
				uploader.start();
			}';
        } else {
            $activeUploadResponse = 'jQuery("#' . $this->uploadOptions['id'] . '").on("click",function(){
				uploader.start();
				return false;
			});';

            $defaultEvents['FilesAdded'] = 'function(uploader, files){
				jQuery("#' . $this->errorContainer . '").hide();
				var upfiles = "";
				plupload.each(files, function(file) {
					upfiles += "<div id=\""+file.id+"\">" + file.name + "(" + plupload.formatSize(file.size) + ")<b></b></div>";
				});
				jQuery("#' . $this->previewContainer . '").' . $appendHtmlType . '(upfiles);
			}';
        }

        $defaultEvents['PostInit'] = 'function(uploader){
             $(document).on("click", ".plupload_file_action", function () {
                $(this).parent().remove();
                uploader.refresh();
            });
            jQuery("#' . $this->errorContainer . '").hide();
			' . $activeUploadResponse . '
		}';

        $defaultEvents['FilesRemoved'] = 'function(uploader, files){
            jQuery.each(files, function(index, file) {
                jQuery("#"+file.id).remove();
            });
		}';

        $defaultEvents['Refresh'] = 'function(uploader){
            var params = uploader.getOption("multipart_params");
            if(params.max_file_nums !== undefined){
                var uploaded_nums = $("#' . $this->previewContainer . '").children().length;
                if (uploaded_nums < params.max_file_nums) {
                    $("#" + uploader.settings.container).show();
                } else {
                    $("#" + uploader.settings.container).hide();
                }
            }
		}';

        $defaultEvents['BeforeUpload'] = 'function(uploader, file){
            jQuery("#"+file.id).find(".plupload_file_mark").addClass("plupload_file_uploading").html("正在上传");
		}';


        $defaultEvents['FileUploaded'] = 'function(uploader, file, res){
			var response = JSON.parse(res.response);
            var responeId = ' . ($this->multiSelection ? 'file.id + " .plupload_file_input"' : '"' . $this->responeElementId . '"') . ';
            jQuery("#" + responeId).val(response.filename);
			jQuery("#" + file.id + " .plupload_file_thumb").html("<img src=\"' . Yii::getAlias('@attachUrl') . '" + response.filename + "\">");
            jQuery("#" + file.id).removeClass("plupload_file_loading");
            jQuery("#" + file.id + " .plupload_file_status").remove();
		}';

        $defaultEvents['UploadComplete'] = 'function(uploader,files){
			uploader.disableBrowse(false);
		}';

        return $defaultEvents;
    }

    /**
     * @return int the max upload size in MB
     */
    protected static function getPHPMaxUploadSize() {
        $upload_max_filesize = (int) (ini_get('upload_max_filesize'));
        $post_max_size = (int) (ini_get('post_max_size'));
        $memory_limit = (int) (ini_get('memory_limit'));
        return min($upload_max_filesize, $post_max_size, $memory_limit);
    }

    protected function getInputValue($responeType = 'json') {
        $uploadFiles = $this->value;
        if ($this->hasModel()) {
            $uploadFiles = Html::getAttributeValue($this->model, $this->attribute);
        }

        if (!is_array($uploadFiles)) {
            $this->showUploadFiles[] = $uploadFiles;
        } else {
            $this->showUploadFiles = $uploadFiles;
        }

        $showUploadFiles = [];
        foreach ($this->showUploadFiles as $file) {
            $showUploadFiles[] = [
                'path' => $file,
                'name' => basename($file)
            ];
        }

        return $responeType == 'json' ? Json::encode($showUploadFiles) : $showUploadFiles;

        return Json::encode($showUploadFiles);


        if (!is_array($uploadFiles)) {
            $uploadFiles[] = $uploadFiles;
        }

        foreach ($uploadFiles as $file) {
            $this->showUploadFiles[] = [
                'path' => $file,
                'name' => basename($file)
            ];
        }

        return Json::encode($this->showUploadFiles);
    }

}
