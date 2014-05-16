<?php
//SIGHT System - Summary Generation
//Chrome Extension v1.0
//By Gabriel Sina - Last update May 16, 2014
/////////////////////////////////////////////
//SIGHT class
class SIGHT{

	//////////CONFIGURATION SECTION////////////////
	private $TEXT_DIRECTORY = 'C:\Users\SIGHT\Documents\LineGraph\ArticlesText'; //directory to save the INPUT text file
	private $EXE_FILE = 'NewGeneration.exe'; //.exe file
	private $EXE_PATH = 'C:\Users\SIGHT\Documents\Visual Studio 2008\Projects\NewGeneration\Debug'; //path of the .exe file
	private $EXE_TXT_OUTPUT_PATH = 'C:\cygwin\home\SIGHT\irnet\FUFSURGE'; //path to the generated summary .txt file
	private $EXE_LAST_LINE_STANDARD = 'Finished generating.'; //standard successful last line message from the pearl script
	//End of configuration section
	
	private $text_body; //string text body
	private $article_id; //article ID

	public $output;

	public function __construct($text_body, $id){
		$this->text_body = $text_body;
		$this->article_id = $id;
	}

	private function saveText(){
		$current_path = getcwd(); //stores current php's working_directory
		//echo ($current_path);
		if(chdir($TEXT_DIRECTORY)) //changes working_directory to text output directory
		{	
			if(file_exists("articleText".$article_id.".txt")){
				chdir($current_path);
				return true;
			}
			else{
				$fp = fopen("articleText".$article_id.".txt", "wb");//creates a new ID.txt file, wb permission
				if ($fp == true){ // tests if the file was created
					fwrite($fp, $text_body); //writes the input text string to the above file
					fclose($fp); //closes file
					chdir($current_path);
					return true;
				}
			}
		}
		chdir($current_path); // sets the working_directory to its previous value
		return false;
	}

	private function readSIGHTOutput(){
		//gets content of the VEM final output (.txt file)
		//'C:\cygwin\home\SIGHT\irnet\FUFSURGE\outputnewgen.txt'
		$output_content = file_get_contents("$EXE_TXT_OUTPUT_PATH\"$article_id\".txt", true);
		$this->output = $output_content;
		return true;
	}

	public function runSIGHT(){
		//if it is possible to save text and image
		if($this->saveText()){
			//Arguments
			$args = "C:\Users\SIGHT\Documents\MATLAB\data\xml\Suggestion\"$article_id\".xml C:\Users\SIGHT\Documents\LineGraph\LinegraphSpreadsheet\Training\"$article_id\"BNresultPar.txt";
			//stores the current working_directory of php
			$current_path = getcwd();
			if(chdir($EXE_PATH))//tries to change the working_directory
			{
				$exec_result = exec($EXE_FILE." ".$args, $output);
				chdir($current_path);
				if($exec_result==$EXE_LAST_LINE_STANDARD) //if the last resulting line from command exec() is what we expected to be
				{
					if (readSIGHTOutput()){//if we could read the output
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