<?php

App::uses('Shell', 'Console');

class CodeCheckTask extends Shell {
	
	public function getFiles($opts = null) {
		$opts = array();
		if (!isset($opts['paths'])) {
			$opts['paths'] = explode(',', $this->params['path']);
		}
		foreach ($opts['paths'] as $k => $v) {
			if (defined($v)) {
				$opts['paths'][$k] = constant($v);
			}
			$opts['paths'][$k] = trim(rtrim($opts['paths'][$k], '/'));
		}
		$this->out('Will check these paths:');
		foreach ($opts['paths'] as $path) {
			$this->out($path);
		}
		$this->out();
		if (!isset($opts['exclude'])) {
			if (isset($this->params['exclude'])) {
				$opts['exclude'] = explode(',', $this->params['exclude']);
			} else {
				$opts['exclude'] = array();
			}	
			foreach ($opts['exclude'] as $k => $v) {
				if (defined($v)) {
					$opts['exclude'][$k] = constant($v);
				}
				$opts['exclude'][$k] = trim(rtrim($opts['exclude'][$k], '/'));
			}
		}
		$this->out('Will exclude these paths:');
		foreach ($opts['exclude'] as $exclude) {
			$this->out($exclude);
		}
		$this->out();
		if (!isset($opts['filse'])) {
			$opts['files'] = explode(',', $this->params['files']);
		}
		$this->out('Will check these files:');
		foreach ($opts['files'] as $file) {
			$this->out('*.'.$file);
		}
		$this->out();
		$files = array();
		foreach ($opts['paths'] as $path) {
			$find = 'find "' . $path . '"';
			if (isset($excludes)) {
				$find .= ' \( -path "*'.$excludes[0].'" ';
				for ($i = 1; $i < count($excludes); $i++) {
					$find .= '-o -path "*'.$excludes[$i].'" ';
				}
				$find .= '\) -prune -not -type d -o';
			}
			$find .= ' -name "*.'.$opts['files'][0].'"';
			for ($i = 1; $i < count($opts['files']); $i++) {
				$find .= ' -o -name "*.'.$opts['files'][$i].'"';
			}
			exec($find, $temp);
			$files = array_merge($files, $temp);
		}
		$files = array_unique($files);
		$files = array_diff($files, array(__FILE__));
		return $files;
	}

	public function getOptionParser($task = false) {
		$this->stdout->styles('option', array('bold' => true));
		$parser = parent::getOptionParser();
		$options = array(
			'path' => array(
				'short' => 'p',
				'default' => 'APP',
				'help' => 'comma-delimited path list (constants or strings)'),
			'exclude' => array(
				'short' => 'e',
				'default' => NULL,
				'help' => 'comma-delimited path exclusion list (constants or strings)'),
			'mode' => array(
				'short' => 'm',
				'default' => 'interactive',
				'choices' => array('interactive', 'diff', 'silent'),
				'help' => "<option>interactive</option> shows errors individually and prompts for change\n" .
				"<option>diff</option> writes a unified diff to current directory without changing files\n" .
				"<option>silent</option> corrects all errors without prompting\n"),
			'files' => array(
				'short' => 'f',
				'default' => 'php',
				'help' => 'comma-delimited extension list, defaults to php'),
			);
		$parser->addOptions($options);
		$parser->description('<info>Ensure code is error-free and follows conventions</info>');
		return $parser;
	}

}
