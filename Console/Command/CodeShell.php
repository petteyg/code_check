<?php
class CodeShell extends Shell {

	/**
	* Shell tasks
	*
	* @var array
	*/
	public $tasks = array(
		'CodeConvention',
		'CodeWhitespace'
	);

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
	public function main()  {
		if (!empty($this->args)) {
			$tasks = array('convention', 'whitespace');
			if (!in_array($this->args[0], $tasks)) {
				$this->out('');
				$this->out('Invalid task "'.$this->args[0].'" specified.');
				$this->out('Perhaps you meant "'.$this->meant($this->args[0], $tasks).'"?');
				die();
			}
			$options = array();
			$options['path'] = !empty($this->params['path']) ? explode(',', $this->params['path']) : array('APP');
			$options['exclude'] = !empty($this->params['exclude']) ? explode(',', $this->params['exclude']) : NULL;
			$options['mode'] = !empty($this->params['mode']) ? $this->params['mode'] : 'interactive';
			$options['files'] = !empty($this->params['files']) ? explode(',', $this->params['files']) : array('php');
			$this->{'Code'.ucfirst($this->args[0])}->execute($options);
		} else {
			$this->out('Usage: cake code task [options]');
			$this->out('');
			$this->out('Tasks:');
			$this->out('convention : checks code for CakePHP conventions');
			$this->out('whitespace : checks files for leading and trailing whitespace');
			$this->out('');
			$this->out('Options:');
			$this->out('-path    : comma-delimited path list (constants or strings), defaults to APP');
			$this->out('-exclude : comma-delimited path exclusion list (constants or strings)');
			$this->out('-mode    : interactive (default) shows errors individually and prompts for change ');
			$this->out('           diff                  writes a unified diff to current directory, does not change any files');
			$this->out('           silent                corrects all errors without prompting');
			$this->out('-files   : comma-delimited extension list, defaults to php');
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