<?php

App::uses('Shell', 'Console');

class CodeConventionTask extends Shell {

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
		foreach	($options['path'] as $path) {
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
		$this->out('in');
		foreach ($paths as $path) {
			$this->out($path);
		}
		$this->out('');
		if (!empty($options['exclude'])) {
			$this->out('excluding');
			$this->out('');
			foreach ($options['exclude'] as $exclude) {
				$this->out($exclude);
			}
		}
		$grep = 'grep -RPnh "%s" "%s"';
		$regex = array();

		$regex['php']['find'] = array('(<'.'\?)\s');
		$regex['php']['replace'] = array('$1php ');
		$regex['function']['find'] = array('(function [a-zA-Z_\x7f\xff][a-zA-Z0-9_\x7f\xff]+) \(');
		$regex['function']['replace'] = array('$1(');
		$regex['control']['find'] = array('if\(', 'foreach\(', 'for\(', 'while\(', 'switch\(', '\)\{');
		$regex['control']['replace'] = array('if (', 'foreach (', 'for (', 'while (', 'switch (', ') {');
		$regex['array']['find'] = array('(^\s)=>(^\s)', '(^\s)=>', '=>(^\s)');
		$regex['array']['replace'] = array('$1 => $2', '$1 =>', '=> $1');
		$regex['deprecated']['find'] = array('([^a-zA-Z0-9_\x7f\xff])del\(', '([^a-zA-Z0-9_\x7f\xff])remove\(');
		$regex['deprecated']['replace'] = array('$1delete(', '$1delete(');
		$regex['wrapper']['find'] = array('(?<!function)([^a-zA-Z0-9_\x7f\xff\`])a\(', '(?<!function)([^a-zA-Z0-9_\x7f\xff])am\(', '(?<!function)([^a-zA-Z0-9_\x7f\xff])e\(([^)]*)\)', '(?<!function)([^a-zA-Z0-9_\x7f\xff])low\(', '(?<!function)([^a-zA-Z0-9_\x7f\xff])up\(', '(?<!function)([^a-zA-Z0-9_\x7f\xff])r\(');
		$regex['wrapper']['replace'] = array('$1array(', '$1array_merge(', '$1echo $2', '$1strtolower(', '$1strtoupper(', '$1str_replace(');
		$regex['space']['find'] = array('\',\'');
		$regex['space']['replace'] = array('\', \'');

		$types = array_keys($regex);

		$modes = array('diff', 'interactive', 'silent');
		if (!in_array($options['mode'], $modes)) {
			$this->out('');
			$this->out('Invalid mode "'.$options['mode'].'" specified.');
			$this->out('Perhaps you meant "'.CodeShell::meant($options['mode'], $modes).'"?');
			die();
		}

		foreach ($files as $file) {
			if (in_array($options['mode'], array('interactive', 'silent'))) {
				$contents = file_get_contents($file);
				foreach ($types as $t) {
					for ($i = 0; $i < count($regex[$t]['find']); $i++) {
						$f = $regex[$t]['find'][$i];
						$grepd = exec(sprintf($grep, $f, $file), $output);
						if (!empty($grepd)) {
							foreach ($output as $line) {
								$this->out('');
								$this->out('');
								$this->out($this->shortPath($file));
								preg_match('/[^\d]*([\d]*)[^:]*:\s*(.*)/', $line, $linecode);
								$linenum = $linecode[1];
								$linecode = $linecode[2];
								$this->out('Line '.str_pad($linenum, 4, "0", STR_PAD_LEFT).': '.$linecode);
								$r = $regex[$t]['replace'][$i];
								$replace = preg_replace('/'.$f.'/', $r, $linecode);
								$this->out('Change to: '.$replace);
								$fix = $this->in('Fix it?', array('y', 'n', 'q'), 'y');
								if ($fix === 'y') {
									$contents = preg_replace('/'.$f.'/', $r, $contents);
									file_put_contents($file, $contents);
								} else if ($fix === 'q') {
									exit();
								}
							unset($output);
							}
						}
					}
				}
			}
		}
	}

}
