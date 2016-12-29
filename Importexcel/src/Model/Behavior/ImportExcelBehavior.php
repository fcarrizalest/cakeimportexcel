<?php
namespace ImportExcel\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Table;

use Cake\Log\Log;

interface ImportExcelInterface
{
    public function parseFromExcelArray($array);
}


/**
 * ImportExcel behavior
 */
class ImportExcelBehavior extends Behavior implements ImportExcelInterface
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
    	'field' => 'excel_file'
    ];


    public function beforeSave( $event,  $entity)
    {



    	Log::write('debug',  'BeforeSave ImportExcelBehavior ' );
		

		Log::write('debug',  'Event:');
		Log::write('debug',  $event );
		Log::write('debug',  'Entity');
		Log::write('debug',  $entity );


    	$config = $this->_config;
    	Log::write('debug',  'Config');
    	Log::write('debug',  $config );

    	$data = $entity->toArray();
        $virtualField = $config['field'];

        Log::write('debug',  'virtualField');
        Log::write('debug',  $virtualField );


    	if ( isset($data[$virtualField]) && is_array($data[$virtualField]) ) {
            // tengo mi field y tengo un array en el field, que deberia ser un archivo
    		$file = $entity->get($virtualField);

    		$error = $this->_triggerErrors($file);


    		if ($error === false) {
              	throw new \Exception("Error Processing Request", 1);
              	
            } elseif (is_string($error)) {
                throw new \ErrorException($error);
            }

            $array = $this->prepareArrayData( $file, $config['options'] );

            if( in_array('ImportExcelInterface', class_implements($this->_table) ) )
            	$this->_table->parseFromExcelArray( $array);
            else
            	$this->parseFromExcelArray($array);
            
           	




        }


    }

    public function parseFromExcelArray( $array){




    }




    private function prepareArrayData($file = null, array $options = [])
    {
        $result = [];

        /**  load and configure PHPExcelReader  * */
        \PHPExcel_Cell::setValueBinder(new \PHPExcel_Cell_AdvancedValueBinder());
        $fileType = \PHPExcel_IOFactory::identify($file);

        if($fileType != 'Excel5' && $fileType != 'Excel2007'){
            $result['Error'] = __d( 'excelimport', 'El formato del archivo no es valido.');
            return $result;
        }

        $PhpExcelReader = \PHPExcel_IOFactory::createReader($fileType);

        $PhpExcelReader->setReadDataOnly(false);

        /** identify worksheets in file * */
        $worksheets = $PhpExcelReader->listWorksheetNames($file);

        $worksheetToLoad = null;

        if(count($worksheets) === 1){
            $worksheetToLoad = $worksheets[0];  //first option: if there is only one worksheet, use it
        }elseif(isset($options['worksheetPosition']) && $options['worksheetPosition'] > -1){
            $worksheetToLoad = $worksheets[$options['worksheetPosition']]; 
        }elseif(isset($options['worksheetName']) && $options['worksheetName'] != ''){
            $worksheetToLoad = $options['worksheetName'];
        }else{
            $result['Error'] = __d( 'excelimport', 'Hoja no especifica.');
            return $result;
        }

        if (!in_array($worksheetToLoad, $worksheets)) {
            throw new MissingTableClassException(__d( 'excelimport', 'No proper named worksheet found'));
        }

        /** load the sheet and convert data to an array */
        $PhpExcelReader->setLoadSheetsOnly($worksheetToLoad);
        $PhpExcel = $PhpExcelReader->load($file);
        $data = $PhpExcel->getSheet(0)->toArray();



        for ($i=$options['startRow']; $i < count($data); $i++) { //start from row n
            
            $tmp = [];
            foreach ($options['headerCols'] as $key => $header) {

                $tmp[$header] = @$data[$i][$key];
                
            }

            
            foreach ($options['notEmpty'] as $key) { //check empty
                if(empty($tmp[$key])){
                    $tmp = []; break;
                }
            }
            

            if(!empty($tmp)){
                $result[] = $tmp;    
            }

        }
        
        
        return $result;
    }


    protected function _triggerErrors($file)
    {
        if (!empty($file['error'])) {
            switch ((int)$file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $message = __('The uploaded file exceeds the upload_max_filesize directive in php.ini : {0}', ini_get('upload_max_filesize'));
                    break;

                case UPLOAD_ERR_FORM_SIZE:
                    $message = __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
                    break;

                case UPLOAD_ERR_NO_FILE:
                    $message = false;
                    break;

                case UPLOAD_ERR_PARTIAL:
                    $message = __('The uploaded file was only partially uploaded.');
                    break;

                case UPLOAD_ERR_NO_TMP_DIR:
                    $message = __('Missing a temporary folder.');
                    break;

                case UPLOAD_ERR_CANT_WRITE:
                    $message = __('Failed to write file to disk.');
                    break;

                case UPLOAD_ERR_EXTENSION:
                    $message = __('A PHP extension stopped the file upload.');
                    break;

                default:
                    $message = __('Unknown upload error.');
            }

            return $message;
        }
    }

}
