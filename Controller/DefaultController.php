<?php

namespace BviFileUploadBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller {
    
    public function indexAction() {
        return $this->render('BviFileUploadBundle:Default:index.html.twig');
    }
    
    //file upload
    
    public function doUploadAction(Request $request) {
        
        if ($request->isMethod('POST')) {
            
            $data['file_key'] = 'banner';
            $data['upload_max_size'] = '500000';
            $data['media_id'] = 2;
            $data['base_path'] = 'uploads/bannerImg';
            $data['watermark'] = true;
            
            $destSzieArr = array();
            $destSzieArr[0]['destW'] = 165;
            $destSzieArr[0]['destH'] = 165;

            $destSzieArr[1]['destW'] = 350;
            $destSzieArr[1]['destH'] = 350;

            $destSzieArr[2]['destW'] = 209;
            $destSzieArr[2]['destH'] = 141;

            $destSzieArr[3]['destW'] = 260;
            $destSzieArr[3]['destH'] = 200;

            $destSzieArr[4]['destW'] = 263;
            $destSzieArr[4]['destH'] = 198;
            
            $data['main_water_mark_path'] = 'waterMark/lms-watermark80_80.png';
            
            $dataDimWaterMark = array();
            $dataDimWaterMark[0] = 'waterMark/lms-watermark80_80.png';
            $dataDimWaterMark[1] = 'waterMark/lms-watermark80_80.png';
            $dataDimWaterMark[2] = 'waterMark/lms-watermark80_80.png';
            $dataDimWaterMark[3] = 'waterMark/lms-watermark80_80.png';
            $dataDimWaterMark[4] = 'waterMark/lms-watermark80_80.png';
            
            $data['resize']   = true;
            $data['dims_arr'] = $destSzieArr;
            //$data['water_dims_img'] = $dataDimWaterMark;
            
            $info = $this->get("bvi.helper.upload")->fileHandler($data,$_FILES);
            
            echo '<pre>';print_r($info);
        }
        echo "uploaded";die;
    }
    
    public function deleteFileAction() {
        $this->get("bvi.helper.upload")->removeFiles('2','uploads/bannerImg','bd956b2095f294b4bc775b2f3ef1ca78.jpg');
    }
}
