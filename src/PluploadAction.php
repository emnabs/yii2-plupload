<?php

namespace emhome\plupload;

use Yii;
use yii\base\Action;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;

/**
 * PluploadAction
 *
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 * @author emhome <emhome@163.com>
 * @since 2.0
 */
class PluploadAction extends Action {

    const FILE_MODE_IMAGE = 'image';
    const FILE_MODE_VIDEO = 'video';

    /**
     * @var string file input name.
     */
    public $inputName = 'file';

    /**
     * @var string the directory to store temporary files during conversion. You may use path alias here.
     * If not set, it will use the "plupload" subdirectory under the application runtime path.
     */
    public $tempPath = '@runtime/plupload';

    /**
     * @var integer the permission to be set for newly created cache files.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * If not set, the permission will be determined by the current environment.
     */
    public $fileMode = self::FILE_MODE_IMAGE;

    /**
     * @var integer the permission to be set for newly created directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0775;

    /**
     * @var callable success callback with signature: `function($filename, $params)`
     */
    public $onComplete;

    /**
     * Initializes the action and ensures the temp path exists.
     */
    public function init() {
        parent::init();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $this->tempPath = Yii::getAlias($this->tempPath);
        if (!is_dir($this->tempPath)) {
            FileHelper::createDirectory($this->tempPath, $this->dirMode, true);
        }
    }

    /**
     * Runs the action.
     * This method displays the view requested by the user.
     * @throws HttpException if the view is invalid
     */
    public function run() {
        $uploadedFile = UploadedFile::getInstanceByName($this->inputName);
        $params = Yii::$app->request->getBodyParams();

        $ext = $uploadedFile->extension ?: pathinfo($params['name'], PATHINFO_EXTENSION);
        if ($this->fileMode == static::FILE_MODE_IMAGE) {
            $ext = $this->realFileType($uploadedFile->tempName);
            if (!in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'bmp'])) {
                throw new ForbiddenHttpException('上传失败。不合法的文件类型！');
            }
            $params['ext'] = $ext;
        }
        $filename = $this->getUnusedPath($this->tempPath . DIRECTORY_SEPARATOR . $uploadedFile->name);
        $isUploadComplete = ChunkUploader::process($uploadedFile, $filename);
        if ($isUploadComplete) {
            if ($this->onComplete) {
                return call_user_func($this->onComplete, $filename, $params);
            } else {
                return [
                    'filename' => $filename,
                    'params' => $params,
                ];
            }
        }
        return null;
    }

    /**
     * Returns an unused file path by adding a filename suffix if necessary.
     * @param string $path
     * @return string
     */
    protected function getUnusedPath($path) {
        $newPath = $path;
        $info = pathinfo($path);
        $suffix = 1;
        while (file_exists($newPath)) {
            $newPath = $info['dirname'] . DIRECTORY_SEPARATOR . "{$info['filename']}_{$suffix}";
            if (isset($info['extension'])) {
                $newPath .= ".{$info['extension']}";
            }
            $suffix++;
        }
        return $newPath;
    }

    /**
     * 获取真实文件类型
     * @param Files[TempName] $filename
     * @return string
     */
    protected function realFileType($filename) {
        $fp = fopen($filename, "rb");
        $bin = fread($fp, 8); //只读2字节
        fclose($fp);
        $typeCode = null;
        if (strpos($bin, 'ftyp') !== false) {
            $vfp = fopen($filename, "rb");
            $vbin = fread($vfp, 11); //只读2字节
            fclose($vfp);
            $ext = str_replace($bin, '', $vbin);
            return $ext;
        }
        $strInfo = @unpack("C2chars", $bin);
        if (is_array($strInfo)) {
            $c = implode($strInfo);
            $typeCode = intval($c);
        }
        $maps = [
            7173 => 'gif',
            6677 => 'bmp',
            13780 => 'png',
            255216 => 'jpg',
            //
            7790 => 'exe',
            7784 => 'midi',
            8297 => 'rar',
            8075 => 'zip',
            6073 => 'htaccess',
            3432 => 'txt',
            70108 => 'txt',
            6063 => 'php',
            6033 => 'html',
            6037 => 'cs',
            8273 => 'avi',
            2669 => 'mkv',
            5048 => 'log',
            9184 => 'log',
        ];
        return isset($maps[$typeCode]) ? $maps[$typeCode] : $typeCode;
    }

}
