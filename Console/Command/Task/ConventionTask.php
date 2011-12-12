<?php

App::uses('Shell', 'Console');
App::uses('CodeCheckTask', 'CodeCheck.Console/Command/Task');

class ConventionTask extends CodeCheckTask {

	public function execute()  {
		$files = parent::getFiles();
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
