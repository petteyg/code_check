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
	public function execute($root = APP)  {
		$Folder = new Folder($root);
		$files = $Folder->findRecursive('.*\.php');
		$files = array_diff($files, array(__FILE__));
		$this->out("Checking *.php in ".$root);
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
							preg_match('/[0-9]+/', $line, $linenumber);
							preg_match('/(?<=:)\s+(.*)/', $line, $linecode);
							$this->out('Line '.str_pad($linenumber[0], 4, "0", STR_PAD_LEFT).': '.$linecode[1]);
							$r = $regex[$t]['replace'][$i];
							$replace = preg_replace('/'.$f.'/', $r, $linecode[1]);
							$this->out('Change to: '.$replace);
							$fix = $this->in('Fix it?', array('y', 'n'), 'y');
							if ($fix) {
								$contents = preg_replace('/'.$f.'/', $r, $contents);
								file_put_contents($file, $contents);
							}
						}
					}
				}
			}
		}
	}

}
?>