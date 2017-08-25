<?php

namespace BviFileUploadBundle\Helper;
use Symfony\Component\DependencyInjection\Container;
use SplFileInfo;
use imageLib;

class UploadHelper {

    private $container;
    private $isWaterMark;
    /**
     * @param \BviFileUploadBundle\Helper\Container $container
     */
    public function __construct(Container $container) {

        $this->container = $container;
    }
    
    /**
     * Detect type of files
     * @param type $minetype
     * @return string
     */
    public function detectMineType($minetype) {

        $mediaType = "unknown";
        $mime_types = array(
            // images
            'image/png' => 'Photo',
            'image/jpeg' => 'Photo',
            'image/jpg' => 'Photo',
            'image/gif' => 'Photo',
            'image/bmp' => 'Photo',
            // audio/video
            'audio/mpeg' => 'Audio',
            'video/mp4' => 'Video',
            'video/x-msvideo' => 'Video',
            'video/quicktime' => 'Video',
            'video/3gpp' => 'Video',
            'video/x-matroska' => 'Video',
            'video/x-flv' => 'Video',
            //doc,xls,txt or pdf files
            'application/pdf' => 'Pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'WordDoc',
            'application/msword' => 'doc',
            'text/plain' => 'txt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'doc',
            'application/vnd.oasis.opendocument.text' => 'doc',
            'application/vnd.ms-powerpoint' => 'ppt'
        );

        if (array_key_exists($minetype, $mime_types)) {

            $mediaType = $mime_types[$minetype];
        }

        return $mediaType;
    }
    
    /**
     * Crops or resizes image and writes it on disk
     * @param type $basicPath
     * @param type $folderId
     * @param type $imgSrc
     * @param type $destW
     * @param type $destH
     * @param type $imageName
     * @param type $waterMarkImgPath
     */
    public function resizeImage($basicPath, $folderId, $imgSrc, $destW, $destH, $imageName,$waterMarkImgPath='') {

        $thumbFolderexists = is_dir($basicPath . '/' . $folderId . '/thumbnails');
        $isMineTypeMatched = true;
        if (!$thumbFolderexists) {
            @mkdir($basicPath . '/' . $folderId . '/thumbnails');
            @chmod($basicPath . '/' . $folderId . '/thumbnails', 0777);
        }

        $sizeSubfolder = $basicPath . '/' . $folderId . "/thumbnails/" . $destW . "x" . $destH;
        $sizeSubImageExists = is_dir($sizeSubfolder);

        if (!$sizeSubImageExists) {
            @mkdir("$sizeSubfolder");
            @chmod("$sizeSubfolder", 0777);
        }

        $subfolder = $basicPath . '/' . $folderId . "/thumbnails/" . $destW . "x" . $destH;
        $sizeImageExists = is_dir($subfolder);

        if (!$sizeImageExists) {
            @mkdir("$subfolder");
            @chmod("$subfolder", 0777);
        }

        @chmod("$imgSrc", 0777);

        $info = new SplFileInfo($imageName);
        $imgType = $info->getExtension();

        $sizeArr = @getimagesize($basicPath . '/' . $folderId . '/' . $imageName);

        $mineTypeImg = isset($sizeArr['mime']) ? $sizeArr['mime'] : '';

        $isMineTypeMatched = $this->checkIsImgFile($imgType, $mineTypeImg);

        $width = $sizeArr[0];
        $height = $sizeArr[1];

        if (($width > $destW || $height > $destH) && $isMineTypeMatched == true) {

            require_once ('Resize/php_image_magician.php');

            $magicianObj = new imageLib($basicPath . '/' . $folderId . '/' . $imageName);

            if (count($magicianObj->getErrors()) == 0) {
                $magicianObj->resizeImage($destW, $destH, '2', true);
                $magicianObj->saveImage($sizeSubfolder . '/' . $imageName, 100);
            }
        } else {
            copy($basicPath . '/' . $folderId . '/' . $imageName, $sizeSubfolder . '/' . $imageName);
        }

        if ($sizeImageExists && $this->isWaterMark == true) {
            
            $sourceImg = $basicPath . '/' . $folderId . "/thumbnails/" . $destW . "x" . $destH . '/' . $imageName;
            $this->applyWaterMarkOnImg($sourceImg, $waterMarkImgPath);
        }
        
    }
    
    /**
     * apply water mark on images
     * @param type $sourceImg
     * @param type $waterMarkImg
     * @return boolean
     */
    public function applyWaterMarkOnImg($sourceImg, $waterMarkImg) {
        
        $stamp = imagecreatefrompng($waterMarkImg);
        $im = imagecreatefromjpeg($sourceImg);
        
        // Set the margins for the stamp and get the height/width of the stamp image
        $marge_right = 8;
        $marge_bottom = 5;
        $sx = imagesx($stamp);
        $sy = imagesy($stamp);

        // Copy the stamp image onto our photo using the margin offsets and the photo 
        // width to calculate positioning of the stamp. 
        imagecopy($im, $stamp, imagesx($im) - $sx - $marge_right, imagesy($im) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));

        // Final processing Creating The Image
        imagejpeg($im, $sourceImg, 100);

        return true;
    }
    
    /**
     * @param type $ext
     * @param type $mine
     * @return boolean
     */
    public function checkIsImgFile($ext, $mine) {

        $isValidImg = false;
        $ext = strtolower($ext);

        if ($ext == 'jpg') {
            $ext = "jpeg";
        }

        if (strpos($mine, $ext) !== false) {
            $isValidImg = true;
        }

        return $isValidImg;
    }
    
    /**
     * @param type $mediaId
     * @param type $basePath
     * @return type
     */
    public function getUploadRootDir($basePath,$mediaId) {

        return __DIR__ . "/../../../../web/$basePath/" . $mediaId;
    }
    /**
     * @param type $mediaId
     * @param type $basePath
     */
    public function makeDir($basePath,$mediaId) {
        if (!is_dir(__DIR__ . '/../../../../web/' . $basePath . '/' . $mediaId)) {
            mkdir(__DIR__ . "/../../../../web/$basePath" . '/' . $mediaId, 0777);
        }
    }
    /**
     * upload file image on web server 
     * @param type $basePath
     * @param type $upload_max_size
     * @param type $filekey
     * @param type $mediaId
     * @return string
     */
    
    public function doUploadImg($basePath,$upload_max_size,$filekey,$i,$mediaId) {
        
        $output = array();
        $this->makeDir($basePath,$mediaId);
        $path    = $this->getUploadRootDir($basePath,$mediaId);
        
        $info    = new SplFileInfo($_FILES[$filekey]['name'][$i]);
        //$imgType = $info->getExtension();
        $imgType = $_FILES[$filekey]['type'][$i];
        $errCode = $_FILES[$filekey]['error'][$i];
        $output['state'] = 'success';
        
        if (isset($_FILES[$filekey]['name'][$i])) {
            //Is file size is less than allowed size.
            if ($_FILES[$filekey]['size'][$i] > $upload_max_size) {
                $output['state'] = 'error';
                $output['msg']   = 'File is too large, you can upload max file size '.$upload_max_size;
                
            }
            if($errCode == 1) {
                $output['state'] = 'error';
                $output['msg']   = 'File is too large, you can upload max file size '.$upload_max_size;
            }

            //allowed file type Server side check
            switch (strtolower($imgType)){

                case 'image/png':
                case 'image/gif':
                case 'image/jpeg':
                case 'image/jpg':
                case 'image/pjpeg':
                case 'image/bmp':                   
                    break;
                default:
                    $output['state'] = 'error';
                    $output['msg']   = 'File extension is not valid, only allowed file png,gif,jpeg files';                    
            }

            $fileExt      = $info->getExtension();
            $randomNumber = md5(uniqid(rand(), true));
            $newFileName  = $randomNumber .".". $fileExt;
            
            if (isset($output['state']) && $output['state'] == 'success' && move_uploaded_file($_FILES[$filekey]['tmp_name'][$i], $path ."/". $newFileName)) {
                $filename = $path ."/". $newFileName; 
                
                if(strtolower($imgType) == 'image/jpeg' || strtolower($imgType) == 'image/jpg' || strtolower($imgType) == 'image/pjpeg') {
                    // Orintation changes 
                    if (function_exists('exif_read_data')) {
                        $exif = @exif_read_data($filename);
                        if($exif && isset($exif['Orientation'])) {
                          $orientation = $exif['Orientation'];
                          if($orientation != 1){
                            $img = imagecreatefromjpeg($filename);
                            $deg = 0;
                            switch ($orientation) {
                              case 3:
                                $deg = 180;
                                break;
                              case 6:
                                $deg = 270;
                                break;
                              case 8:
                                $deg = 90;
                                break;
                            }
                            if ($deg) {
                              $img = imagerotate($img, $deg, 0);
                            }
                            // then rewrite the rotated image back to the disk as $filename 
                            imagejpeg($img, $filename, 95);
                          } // if there is some rotation necessary
                        } // if have the exif orientation info
                      } // if function exists
                }  
                
                $output['state']    = 'success';
                $output['filename'] = $newFileName;
                $output['msg']      = 'Uploaded';
            } else {
                $output['state'] = 'error';
                $output['filename'] = $newFileName;
                $output['msg']   = 'There is problem to upload file, please try after some time!';
            }
        }
        return $output;
    }
    
    /**
     * upload file doc and other on web server
     * @param type $basePath
     * @param type $upload_max_size
     * @param type $filekey
     * @param type $mediaId
     * @return string
     */
    
    public function doUploadDocOtherFile($basePath,$upload_max_size,$filekey,$i,$mediaId) {
        
        $output = array();
        $this->makeDir($basePath,$mediaId);
        $path    = $this->getUploadRootDir($basePath,$mediaId);
        
        $info    = new SplFileInfo($_FILES[$filekey]['name'][$i]);
        
        $fileType= $_FILES[$filekey]['type'][$i];
        $errCode = $_FILES[$filekey]['error'][$i];
        $output['state'] = 'success';
        
        if (isset($_FILES[$filekey]['name'][$i])) {
            //Is file size is less than allowed size.
            if ($_FILES[$filekey]['size'][$i] > $upload_max_size) {
                $output['state'] = 'error';
                $output['msg']   = 'File is too large, you can upload max file size '.$upload_max_size;
                
            }
            if($errCode == 1) {
                $output['state'] = 'error';
                $output['msg']   = 'File is too large, you can upload max file size '.$upload_max_size;
            }

            //allowed file type Server side check
            switch (strtolower($fileType)){

                case 'audio/mpeg':
                case 'video/mp4':
                case 'video/x-msvideo':
                case 'video/quicktime':
                case 'video/3gpp':
                case 'video/x-matroska':
                case 'video/x-flv':
                case 'application/pdf':
                case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                case 'application/msword':
                case 'text/plain':
                case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                case 'application/vnd.oasis.opendocument.text':
                case 'application/vnd.ms-powerpoint':                   
                    break;
                default:
                    $output['state'] = 'error';
                    $output['msg']   = 'File extension is not valid, only allowed file video,audio,txt,doc.docx,pdf,worddoc,ppt files';
            }

            $fileExt      = $info->getExtension();
            $randomNumber = md5(uniqid(rand(), true));
            $newFileName  = $randomNumber .".". $fileExt;
            
            if (isset($output['state']) && $output['state'] == 'success'  && move_uploaded_file($_FILES[$filekey]['tmp_name'][$i], $path ."/". $newFileName)) {
                $output['state']    = 'success';
                $output['filename'] = $newFileName;
                $output['msg']      = 'Uploaded';
            } else {
                $output['state'] = 'error';
                $output['filename'] = $newFileName;
                $output['msg']   = isset($output['msg']) ? $output['msg'] : 'There is problem to upload file, please try after some time!';
            }
        }
        return $output;
    }
    
    /**
     * Remove file from folder
     * @param type $mediaId
     * @param type $basePath
     * @param type $fileName
     * @return type
     */
    public function removeFiles($mediaId ,$basePath,$fileName) {
        
        $arr = array();
        $fileOriginPath = __DIR__ . '/../../../../web/'.$basePath.'/' . $mediaId;
        if (file_exists($fileOriginPath)) {
            if (file_exists(__DIR__ . '/../../../../web/' . $basePath . '/' . $mediaId . '/thumbnails')) {
                $dirs = array_filter(glob(__DIR__ . '/../../../../web/' . $basePath . '/' . $mediaId . '/thumbnails/*'), 'is_dir');
                foreach ($dirs as $filePath) {
                    $arr[basename($filePath)] = (file_exists($filePath . '/' . $fileName)) ? unlink($filePath . '/' . $fileName) : '0';
                }
            }
            $arr['original'] = (file_exists($fileOriginPath . '/' . $fileName)) ? unlink($fileOriginPath . '/' . $fileName) : '0';
        }
        return $arr;
    }
    
    
    /**
     * File handler to operate file operations 
     * @param type $data
     * @param type $files
     * @return type
     */
    
    public function fileHandler($data = array(),$files) {
        
        $file_key        = $data['file_key'];
        $upload_max_size = $data['upload_max_size'];
        $media_id        = $data['media_id'];
        $resize          = isset($data['resize']) ? true: false;
        $destSzieArr     = isset($data['dims_arr']) ? $data['dims_arr'] : '';
        $wtrMrkDim       = isset($data['water_dims_img']) ? $data['water_dims_img'] : '';
        $basicPath       = $data['base_path'];
        $this->isWaterMark = isset($data['watermark']) ? true : false;
        $water_mark_pth  = isset($data['main_water_mark_path']) ? $data['main_water_mark_path'] : '' ;
        $k               = 0;   
        $result          = array();
        $mainImg         = '';
        
        if (isset($files[$file_key]) && is_array($files[$file_key]['name'])) {

            $totalFiles = count($files[$file_key]['name']);

            for ($i = 0; $i < $totalFiles; $i++) {

                $mediaType = $this->detectMineType($files[$file_key]['type'][$i]);
                
                if ($mediaType == 'Photo') {
                    $output = $this->doUploadImg($basicPath,$upload_max_size, $file_key,$i, $media_id);
                    $mainImg = $output['filename'];

                    if ($resize == true && $output['state'] != 'error') {

                        foreach ($destSzieArr as $val) {
                            $imgSrc = "$basicPath/" . $media_id . "/" . $output['filename'];
                            $destW = $val['destW'];
                            $destH = $val['destH'];
                            
                            $wtrMrkDimImg = '';
                            if($this->isWaterMark == true) {
                                $wtrMrkDimImg = isset($wtrMrkDim[$k]) && $wtrMrkDim[$k]!='' ? $wtrMrkDim[$k] : '';
                                if($wtrMrkDimImg == ''){
                                    $wtrMrkDimImg = $water_mark_pth;
                                }
                            }
                            
                            $k++;
                            $this->resizeImage($basicPath, $media_id, $imgSrc, $destW, $destH, $output['filename'],$wtrMrkDimImg);
                        }
                    }
                    $result['filename'] = $mainImg;
                    $result['media_type'] = $mediaType;
                    $result['state'] =  $output['state'];
                    $result['msg'] =  $output['msg'];
                
                }else{
                    $output = $this->doUploadDocOtherFile($basicPath,$upload_max_size, $file_key,$i, $media_id);
                    $result['filename'] = $mainImg;
                    $result['media_type'] = $mediaType;
                    $result['state'] =  $output['state'];
                    $result['msg'] =  $output['msg'];
                }
                if ($mediaType == 'Photo' && $totalFiles > 0 && $mainImg != '' && $this->isWaterMark == true) {
                    //apply water on main image
                    $sourceImg = $basicPath.'/' . $media_id . '/' . $mainImg;
                    $this->applyWaterMarkOnImg($sourceImg, $water_mark_pth);
                }
            }
            return $result;
        }
    }
    
}