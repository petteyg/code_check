<?php
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
		$find = 'find '.$options['path'].' -name "*.'.$options['files'][0].'"';
		for ($exts = 1; $exts < count($options['files']); $exts++) {
			$find .= ' -o -name "*.'.$options['files'][$exts].'"';
		}
		exec($find, $files);
		$files = array_diff($files, array(__FILE__));
		$this->out('Checking ', false);
		foreach ($options['files'] as $ext) {
			$this->out('*.'.$ext.' ', false);
		}
		$this->out('in '.$options['path']);
		$grep = 'grep -RPnh "%s" %s';
		$regex = array();

		$regex['array']['find'] = array('(^\s)=>(^\s)', '(^\s)=>', '=>(^\s)');
		$regex['array']['replace'] = array('$1 => $2', '$1 =>', '=> $1');
		$regex['control']['find'] = array('if\(', 'foreach\(', 'for\(', 'while\(', 'switch\(', '\)\{');
		$regex['control']['replace'] = array('if (', 'foreach (', 'for (', 'while (', 'switch (', ') {');
		$regex['function']['find'] = array('(function [a-zA-Z_\x7f\xff][a-zA-Z0-9_\x7f\xff]+) \(');
		$regex['function']['replace'] = array('$1(');
		$regex['php']['find'] = array('(<'.'\?)\s');
		$regex['php']['replace'] = array('$1php ');

		$types = array_keys($regex);

		if (!in_array($options['mode'], array('diff', 'interactive', 'silent'))) {
			$this->out('');
			$this->out('Invalid mode "'.$options['mode'].'" specified.');
			$modes = array('interactive', 'diff', 'silent');
			$this->out('Perhaps you meant "'.$this->meant($options['mode'], $modes).'"?');
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

	public function meant($in, $params) {
		$meant = array();
		foreach ($params as $param) {
			$meant[levenshtein($in, $param)] = $param;
		}
		ksort($meant);
		return array_shift($meant);
	}

}
?>