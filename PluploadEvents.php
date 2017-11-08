<?php

namespace emhome\plupload;

use Yii;

/**
 * PluploadEvents
 *
 * @author emhome <emhome@163.com>
 * @since 2.0
 */
class PluploadEvents extends \yii\base\Object {

    const JQUERY = 'jQuery';

    public $previewContainer = 'previewContainer';
    public $errorContainer = 'errorContainer';
    public $uploadOptions;
    public $multiSelection;
    public $autoUpload;
    public $responeElement;
    public $attachUrl;
    //
    private $appendHtmlType = 'html';
    private $activeUploadResponse = '';

    public function getScripts($events) {
        $registerEvents = [];
        foreach ($events as $method_name) {
            $method_name = ucfirst($method_name);
            $method = 'bind' . $method_name;
            if (!method_exists($this, $method)) {
                continue;
            }
            $registerEvents[$method_name] = $this->$method();
        }

        return $registerEvents;
    }

    /**
     * Init
     */
    protected function bindInit() {
        return 'function(uploader){
            var params = uploader.getOption("multipart_params");
            var elementBrowse = ' . self::JQUERY . '("#" + uploader.settings.container);
            if(params.max_file_nums !== undefined){
                var uploaded_nums = ' . self::JQUERY . '("#' . $this->previewContainer . '").children().length;
                if (uploaded_nums >= params.max_file_nums) {
                    elementBrowse.hide();
                }
            }
        }';
    }

    /**
     * PostInit
     */
    protected function bindPostInit() {
        if (!$this->autoUpload) {
            $this->activeUploadResponse = self::JQUERY . '("#' . $this->uploadOptions['id'] . '").on("click",function(){
                uploader.start();
                return false;
            });';
        }
        return 'function(uploader){
            ' . self::JQUERY . '(document).on("click", ".plupload_file_action", function () {
                ' . self::JQUERY . '(this).parent().remove();
                uploader.refresh();
            });
            ' . self::JQUERY . '("#' . $this->errorContainer . '").hide();
            ' . $this->activeUploadResponse . '
        }';
    }

    /**
     * Browse
     */
    protected function bindBrowse($up) {
        return;
    }

    /**
     * Refresh
     */
    protected function bindRefresh() {
        return 'function(uploader){
            var params = uploader.getOption("multipart_params");
            var elementBrowse = ' . self::JQUERY . '("#" + uploader.settings.container);
            if(params.max_file_nums !== undefined){
                var uploaded_nums = ' . self::JQUERY . '("#' . $this->previewContainer . '").children().length;
                if (uploaded_nums < params.max_file_nums) {
                    elementBrowse.show();
                } else {
                    elementBrowse.hide();
                }
            }
        }';
    }

    /**
     * StateChanged
     */
    protected function bindStateChanged($up) {
        return;
    }

    /**
     * QueueChanged
     */
    protected function bindQueueChanged($up) {
        return;
    }

    /**
     * OptionChanged
     */
    protected function bindOptionChanged($up, $name, $value, $oldValue) {
        return;
    }

    /**
     * BeforeUpload
     */
    protected function bindBeforeUpload() {
        return 'function(uploader, file){
            ' . self::JQUERY . '("#" + file.id).find(".plupload_file_mark").addClass("plupload_file_uploading").html("正在上传");
        }';
    }

    /**
     * UploadProgress
     */
    protected function bindUploadProgress() {
        return 'function(uploader, file){
            var percent = file.percent + "%";
            var elementFile = ' . self::JQUERY . '("#" + file.id);
            elementFile.find(".plupload_file_percent").html(percent);
            elementFile.find(".progress-bar").width(percent);
        }';
    }

    /**
     * FileFiltered
     */
    protected function bindFileFiltered($up, $file) {
        return;
    }

    /**
     * FilesAdded
     */
    protected function bindFilesAdded() {
        $disableBrowse = 'true';
        if ($this->multiSelection) {
            $this->appendHtmlType = 'append';
            $disableBrowse = 'false';
        }
        $script = 'uploader.disableBrowse(' . $disableBrowse . ');';
        if ($this->autoUpload) {
            $script .= 'uploader.start();';
        }
        return 'function(uploader, files){
            ' . self::JQUERY . '("#' . $this->errorContainer . '").hide();
            var upfiles = "";
            plupload.each(files, function(file) {
                upfiles += PluploadCustom.tplUploadItem(file,' . ($this->multiSelection ? 'true' : 'false') . ');
            });
            ' . self::JQUERY . '(document).on("click", ".plupload_file_action", function () {
                var id = ' . self::JQUERY . '(this).parent().attr("id");
                uploader.removeFile(id);
            });
            ' . self::JQUERY . '("#' . $this->previewContainer . '").' . $this->appendHtmlType . '(upfiles);
            uploader.refresh();
            ' . $script . '
        }';
    }

    /**
     * FilesRemoved
     */
    protected function bindFilesRemoved() {
        return 'function(uploader, files){
            ' . self::JQUERY . '.each(files, function(index, file) {
                ' . self::JQUERY . '("#" + file.id).remove();
            });
        }';
    }

    /**
     * FileUploaded
     */
    protected function bindFileUploaded() {
        $responeElement = $this->multiSelection ? 'elementFile.find(".plupload_file_input")' : self::JQUERY . '("#' . $this->responeElement . '")';
        return 'function(uploader, file, res){
            var response = JSON.parse(res.response);
            var elementFile = ' . self::JQUERY . '("#" + file.id);
            var responeElement = ' . $responeElement . ';
            responeElement.val(response.filename);
            elementFile.find(".plupload_file_thumb img").attr("src", "' . $this->attachUrl . '" + response.filename);
            elementFile.removeClass("plupload_file_loading");
            elementFile.find(".plupload_file_status").remove();
        }';
    }

    /**
     * ChunkUploaded
     */
    protected function bindChunkUploaded($up, $file, $info) {
        return;
    }

    /**
     * UploadComplete
     */
    protected function bindUploadComplete() {
        return 'function(uploader,files){
            uploader.disableBrowse(false);
        }';
    }

    /**
     * Destroy
     */
    protected function bindDestroy($up) {
        return;
    }

    /**
     * Error
     */
    protected function bindError() {
        return 'function(uploader, error){
            var errorElement = ' . self::JQUERY . '(uploader.settings.error_container);
            errorElement.html("Error #:"+error.code+" "+error.message).show();
        }';
    }

}
