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
	public function execute($options)  {
		$Folder = new Folder($options['path']);
		$files = $Folder->findRecursive('.*\.('.implode('|', $options['files']).')');
		$this->out("Checking ", false);
		foreach ($options['files'] as $ext) {
			$this->out('*.'.$ext.' ', false);
		}
		$this->out('in '.$options['path']);
		foreach ($files as $file) {
			$contents = file_get_contents($file);
			if (preg_match('/^[\n\r|\n\r|\n|\r|\s]+\<\?php/', $contents)) {
				$this->out('Leading whitespace: '. $this->shortPath($file));
			}
			if (preg_match('/\?\>[\n\r|\n\r|\n|\r|\s]+$/', $contents)) {
				$this->out('Trailing whitespace: '. $this->shortPath($file));
			}
		}
	}

}
?>