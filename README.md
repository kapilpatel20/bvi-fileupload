*******Documentation********
Run below command to install from composer

composer require kapilpatel20/bvi-fileupload dev-master

Add bundle in AppKernel.php in registerBundles function

new BviFileUploadBundle\BviFileUploadBundle(),

******General Settings**********

<!-- $data['file_key'] = 'YOUR_FILE_NAME_KEY'; 
$data['upload_max_size'] = 'UPLOAD_MAX_FILE_SIZE';
$data['media_id'] = 'DIR_NAME OR FOLDER ID';
$data['base_path'] = 'BASIC_UPLOAD_PATH';
-->

//Water mark tag settings

$data['watermark'] = true or false;
$data['main_water_mark_path'] = // Main water mark thumb image if watermark option true


//Upload file

$info = $this->get("bvi.helper.upload")->fileHandler($data,$_FILES);

params $data as configuration array
params $_FILES as file object

//Generate Thumbs settings

***For example**************
$sizeArr = array();
$sizeArr[0]['destW'] = 165;
$sizeArr[0]['destH'] = 165;

$sizeArr[1]['destW'] = 350;
$sizeArr[1]['destH'] = 350;

Here params or index key are as:
 
destW is width of thumb image
destH is height of thumb image

$data['resize']   = true;
$data['dims_arr'] = $sizeArr;

//Upload and generate thumbs file

$info = $this->get("bvi.helper.upload")->fileHandler($data,$_FILES);

//If you want to apply water mark as per generate thumbs:

$dataDimWaterMark    = array();
$dataDimWaterMark[0] = 'WATER MARK IMAGE PATH ONE';
$dataDimWaterMark[1] = 'WATER MARK IMAGE PATH TWO';

$data['water_dims_img'] = $dataDimWaterMark;

*/

// Delete a file

$this->get("bvi.helper.upload")->removeFiles('MEDIA_ID','BASIC_PATH','FILE_NAME');


**/