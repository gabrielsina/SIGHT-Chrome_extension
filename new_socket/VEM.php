<?php
//SIGHT System - Summary Generation
//Chrome Extension v1.0
//By Gabriel Sina - Last update May 16, 2014
/////////////////////////////////////////////
//Visual Extraction Module class
class VEM{

	//////////CONFIGURATION SECTION////////////////
	private $IMAGE_DIRECTORY = 'C:\Users\SIGHT\Desktop\DemoLineGraph\Input_images'; //directory to save the INPUT image
	private $TEXT_DIRECTORY = 'C:\\Users\\SIGHT\\Documents\\LineGraph\\ArticlesText'; //directory to save the INPUT text file
	private $PEARL_SCRIPT_NAME = 'LinkAllChromeExtension.pl'; //pearl script name
	private $PEARL_SCRIPT_PATH = 'C:\Users\SIGHT\Documents\LineGraph\LinkAll'; //path of the pearl file
	private $PEARL_TXT_OUTPUT_PATH = 'C:\cygwin\home\SIGHT\irnet\FUFSURGE'; //path to the generated summary .txt file
	private $PEARL_LAST_LINE_STANDARD = 'Finished generating.'; //standard successful last line message from the pearl script

	//most relevant paragraph output path
	//so far, the output file will always be overwritten, once it is fixed in the Perl script, it should be changed here
	private $MOST_RELEVANT_PATH = 'C:\Users\SIGHT\Documents\LineGraph\output\MRPresult.txt';
	//End of configuration section

	private $image_url; //image url
	private $text_body; //string text body

	public $text_n_image_id; // id for this tuple VEM<image,text>

	public $output; // summary output text
	public $mrp_content; // number of the most relevant paragraph

	public function __construct($image_url, $text_body, $id){
		$this->image_url = $image_url;
		$this->text_body = $text_body;
		//$this->text_n_image_id = uniqid(rand());
		$this->text_n_image_id = isset($_POST[$id]);
		//$this->TEXT_DIRECTORY = 'C:\\Users\\SIGHT\\Documents\\LineGraph\\ArticlesText';
	}

	private function saveText(){
		$current_path = getcwd(); //stores current php's working_directory
		//echo ($current_path);
		if(chdir('C:\\Users\\SIGHT\\Documents\\LineGraph\\ArticlesText')) //changes working_directory to text output directory
		{
			$fp = fopen("articleText".$text_n_image_id.".txt", "wb");//creates a new ID.txt file, wb permission
			if ($fp == true){ // tests if the file was created
				fwrite($fp, $text_body); //writes the input text string to the above file
				fclose($fp); //closes file
				chdir($current_path);
				return true;
			}
		}
		chdir($current_path); // sets the working_directory to its previous value
		return false;
	}

	private function saveImage(){
		//get content of the input image
		$content = file_get_contents($image_url);
		//stores current php's working_directory
		$current_path = getcwd();
		if(chdir($IMAGE_DIRECTORY)) //changes working_directory to image output directory
		{
			//creates a new ID.gif file, wb permission
			$fp = fopen($text_n_image_id.".gif", "wb");
			if ($fp == true){ // tests if the image file was created
				fwrite($fp, $content); //writes the image file
				fclose($fp); //closes file
				chdir($current_path);
				return true;
			}
		}
		chdir($current_path); // sets the working_directory to its previous value
		return false;
	}

	//Reads summary output .txt file
	private function readVEMOutput(){
		//gets content of the VEM final output (.txt file)
		$output_content = file_get_contents($PEARL_TXT_OUTPUT_PATH."\\"."outputgen".$text_n_image_id."txt", true);
		$this->output = $output_content;
		return true;
	}

	//Reads Most Relevant Paragraph .txt file
	private function readMRPOutput(){
		//gets content of the MRP final output (.txt file)
		$mrp_content = file_get_contents($MOST_RELEVANT_PATH, true);
		$this->mrp = $mrp_content;
		return true;
	}

	public function runVEM(){
		//if it is possible to save text and image
		if($this->saveText() && $this->saveImage()){
			//Arguments
			$extension = '.gif';////////TODO: code extension recognition
			$args = "$IMAGE_DIRECTORY\"$text_n_image_id\"$extension $TEXT_DIRECTORY";
			//$args = "$text_n_image_id\" $IMAGE_DIRECTORY\" $extension";
			//stores the current working_directory of php
			$current_path = getcwd();
			if(chdir($PEARL_SCRIPT_PATH))//tries to change the working_directory
			{
				$exec_result = exec($PEARL_SCRIPT_NAME." ".$args, $output);
				chdir($current_path);
				//print_r($output);
				if($exec_result==$PEARL_LAST_LINE_STANDARD) //if the last resulting line from command exec() is what we expected to be
				{
					if (readVEMOutput() && readMRPoutput() ){//if we could read the summary output AND the most relevant paragraph file
						return true;
					}
				}
				return false;
			}
			chdir($current_path);
			return false;

		}
		return false;

	}

}

?>