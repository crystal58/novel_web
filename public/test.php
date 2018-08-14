<?php
$finfo = finfo_open(FILEINFO_MIME, "/usr/share/misc/magic");
if($finfo){
    $filename = "/fastdfs/storage/data/00/00/CtM3BFoC2SWAV7FTAAHl0x27IkI150.png";
    echo finfo_file($finfo, $filename);

    /* 关闭资源 */
    finfo_close($finfo);

}
function getMimeType($fileInfo) {
        $finfo = finfo_open(FILEINFO_MIME, "/usr/share/misc/magic"); // return mime type ala mimetype extension

        if (!$finfo) {
            throw new Exception("Opening fileinfo database failed");
        }

        $filename = $fileInfo["tmp_name"];

        $fileInfo = finfo_file($finfo, $filename);

        $fileInfo = explode("; ", $fileInfo);
        $type = "";
        if (is_array($fileInfo)) {
            $type = $fileInfo[0];
        }

        finfo_close($finfo);

        return $type;
    }
