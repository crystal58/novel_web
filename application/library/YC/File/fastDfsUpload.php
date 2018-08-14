<?php
namespace YC\File;
/**
 * Created by PhpStorm.
 * User: zhangyapin
 * Date: 17/12/6
 * Time: 下午6:16
 */
class fastDfsUpload{
    public function store($file,$suffix){
        $fileInfo = \fastdfs_storage_upload_by_filename1($file,$suffix);
        return $fileInfo;
    }

    public function get($fileId){
        return \fastdfs_storage_download_file_to_buff1($fileId);;
    }
}
