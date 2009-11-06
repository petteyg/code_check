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
		$Folder = new Folder($options['path']);
		$files = $Folder->findRecursive('.*\.('.implode('|', $options['files']).')');
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

		$types = array_keys($regex);

		foreach ($files as $file) {
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
?>