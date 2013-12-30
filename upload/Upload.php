<?php 
/**
 * User: pmsun
 * Date: 13-12-30
 * Time: 下午9:16
 * Desc: 图片上传类
 * Usage:
 * $upload = new UploadClass('xxx', $savefile, array('jpg','jpeg','png','gif'));
 * $upload->upload();
 */
class UploadClass {

    /*
     * 上传input框的name
     * @var string
     */
    protected $uploadName;

    /*
     * 上传图片的信息
     * @var array
     */
    protected $uploadInfo;

    /*
     * 上传图片的保存路径
     * @var string
     */
    protected $saveFilePath;

    /*
     * 允许的文件
     * @var array
     */
    protected $ext;

    /*
     * 上传文件的最大值
     * @var int
     */
    protected $maxFileSize;

    /*
     * 是否改变文件名
     * $var int
     */
    protected $isChange;

    /*
     * 上传成功后的返回信息
     * @var array
     */
    protected $returnFileInfo;


    public function __construct($uploadName, $saveFilePath, $ext = array(), $isChange = true, $maxFileSize = 2097152) {
        $this->uploadInfo = $_FILES[$uploadName];
        $this->saveFilePath = $saveFilePath;
        $this->ext = $ext;
        $this->maxFileSize = $maxFileSize;
        $this->isChange = $isChange;
    }

    public function upload() {
        $msg = array();
        if ($this->uploadInfo['error'] == 0) {
            if (empty($this->uploadInfo['name'])) {
                $msg = array(
                    'status' => 0,
                    'msg'    => '请选择要上传的图片',
                );
            }

            $extension = $this->_getExt($this->uploadInfo['name']);
            if (!$this->_checkType($extension) || !is_uploaded_file($this->uploadInfo['tmp_name'])) {
                $msg = array(
                    'status' => 0,
                    'msg'    => '非法文件',
                );
            }

            if (!$this->_checkSize($this->uploadInfo['size'])) {
                $maxFileSize = $this->maxFileSize / 1024;
                $msg = array(
                    'status' => 0,
                    'msg'    => '最大只能上传' . $maxFileSize . 'MB大小的文件',
                );
            }
            if (empty($msg)) {
                if ($this->isChange) {
                    $filename = md5(time() . $this->uploadInfo['name']) . "." . $extension;
                } else {
                    $filename = $this->uploadInfo['name'];
                }
                $saveFile = $this->saveFilePath . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d');
                if (!file_exists($saveFile)) {
                    mkdir($saveFile, 0755, 1);
                }
                if (!move_uploaded_file($this->uploadInfo['tmp_name'], $saveFile . DIRECTORY_SEPARATOR . $filename)) {
                    $msg = array(
                        'status' => 0,
                        'msg'    => '上传失败',
                    );
                } else {
                    $msg = array(
                        'name'      => $filename,
                        'ext'       => $extension,
                        'size'      => $this->uploadInfo['size'],
                        'save_path' => $saveFile,
                    );
                }
            }
        } else {
            $msg = array(
                'status' => 0,
                'msg'    => '上传失败',
            );
        }
        echo $this->_error($msg);
    }

    /*
     * 监测上传的文件，是否符合要求
     * @param $ext array
     * @return bool
     */
    private function _checkType($extision) {
        return in_array($extision, $this->ext) || empty($this->ext) ? true : false;
    }

    /*
     * 监测上传文件的大小是否符合要求
     * @param $size int
     * @return bool
     */
    private function _checkSize($size) {
        return $size <= $this->maxFileSize ? true : false;
    }

    /*
     * 获取拓展名
     * @param $file string
     * @return string
     */
    private function _getExt($file) {
        $ext = pathinfo($file);
        return $ext['extension'];
    }

    private function _error($data) {
        return json_encode($data);
    }
}
