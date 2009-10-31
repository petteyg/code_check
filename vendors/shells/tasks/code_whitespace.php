<?php
class CodeWhitespaceTask extends Shell {

	/**
	* Models used by shell
	*
	* @var array
	*/
	public $uses = array();

	/**
	* Main execution function
	*
	* @return void
	*/
	public function execute($root = APP)  {
		$Folder = new Folder($root);
		$files = $Folder->findRecursive('.*\.php');
		$this->out("Checking *.php in ".$root);
		foreach ($files as $file) {
			$contents = file_get_contents($file);
			if (preg_match('/^[\n\r|\n\r|\n|\r|\s]+\<\?php/', $contents)) {
				$this->out('!!!contains leading whitespaces: '. $this->shortPath($file));
			}
			if (preg_match('/\?\>[\n\r|\n\r|\n|\r|\s]+$/', $contents)) {
				$this->out('!!!contains trailing whitespaces: '. $this->shortPath($file));
			}
		}
	}

}
?>