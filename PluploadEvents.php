<?php

namespace emhome\plupload;

use Yii;

class PluploadEvents extends InputWidget {

    public function PostInit() {
        // Called after initialization is finished and internal event handlers bound
        log('[PostInit]');
    }

    public function Browse($up) {
        // Called when file picker is clicked
        log('[Browse]');
    }

    public function Refresh($up) {
        // Called when the position or dimensions of the picker change
        log('[Refresh]');
    }

    public function StateChanged($up) {
        // Called when the state of the queue is changed
        log('[StateChanged]');
    }

    public function QueueChanged($up) {
        // Called when queue is changed by adding or removing files
        log('[QueueChanged]');
    }

    public function OptionChanged($up, $name, $value, $oldValue) {
        // Called when one of the configuration options is changed
        log('[OptionChanged]');
    }

    public function BeforeUpload($up, $file) {
        // Called right before the upload for a given file starts, can be used to cancel it if required
        log('[BeforeUpload]');
    }

    public function UploadProgress($up, $file) {
        // Called while file is being uploaded
        log('[UploadProgress]');
    }

    public function FileFiltered($up, $file) {
        // Called when file successfully files all the filters
        log('[FileFiltered]');
    }

    public function FilesAdded($up, $files) {
        // Called when files are added to queue
        log('[FilesAdded]');
    }

    public function FilesRemoved($up, $files) {
        // Called when files are removed from queue
        log('[FilesRemoved]');
    }

    public function FileUploaded($up, $file, $info) {
        // Called when file has finished uploading
        log('[FileUploaded] File:');
    }

    public function ChunkUploaded($up, $file, $info) {
        // Called when file chunk has finished uploading
        log('[ChunkUploaded] File:');
    }

    public function UploadComplete($up, $files) {
        // Called when all files are either uploaded or failed
        log('[UploadComplete]');
    }

    public function Destroy($up) {
        // Called when uploader is destroyed
        log('[Destroy] ');
    }

    public function Error($up, $args) {
        // Called when error occurs
        log('[Error] ', $args);
    }

}
