<?php

App::uses('Shell', 'Console');
App::uses('CodeCheckTask', 'CodeCheck.Console/Command/Task');

class WhitespaceTask extends CodeCheckTask {

	public function execute() {
		$files = parent::getFiles();
		foreach ($files as $file) {
			$contents = file_get_contents($file);
			if (preg_match('/^[\n\r|\n\r|\n|\r|\s]+\<\?php/', $contents)) {
				$this->out('Leading whitespace: '. $file);
			}
			if (preg_match('/\?\>[\n\r|\n\r|\n|\r|\s]+$/', $contents)) {
				$this->out('Trailing whitespace: '. $file);
			}
		}
	}

}
