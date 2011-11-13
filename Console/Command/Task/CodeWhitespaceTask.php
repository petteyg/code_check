<?php

App::uses('Shell', 'Console');

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
		$paths = array();
		foreach ($options['path'] as $path) {
			if (defined($path)) {
				$paths[] = constant($path);
			} else {
				$paths[] = $path;
			}
		}
		$excludes = array();
		if (!empty($options['exclude'])) {
			foreach ($options['exclude'] as $exclude) {
				if (defined($exclude)) {
					$excludes[] = constant($exclude);
				} else {
					$excludes[] = $exclude;
				}
			}
		}
		$files = array();
		foreach ($paths as $path) {
			$find = 'find "' . $path . '"';
			if (!empty($excludes)) {
				$find .= ' \( -path "*'.$excludes[0].'" ';
				for ($i = 1; $i < count($excludes); $i++) {
					$find .= '-o -path "*'.$excludes[$i].'" ';
				}
				$find .= '\) -prune -not -type d -o';
			}
			$find .= ' -name "*.'.$options['files'][0].'"';
			for ($i = 1; $i < count($options['files']); $i++) {
				$find .= ' -o -name "*.'.$options['files'][$i].'"';
			}
			exec($find, $temp);
			$files = array_merge($files, $temp);
		}
		$files = array_unique($files);
		$files = array_diff($files, array(__FILE__));
		$this->out('Checking ', false);
		foreach ($options['files'] as $ext) {
			$this->out('*.'.$ext.' ', false);
		}
		$this->out('');
		if (!empty($options['exclude'])) {
			$this->out('excluding');
			$this->out('');
			foreach ($options['exclude'] as $exclude) {
				$this->out($exclude);
			}
		}
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
