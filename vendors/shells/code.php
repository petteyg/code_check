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
			$options = array();
			$options['path'] = !empty($this->params['path']) ? (defined($this->params['path']) ? constant($this->params['path']) : $this->params['path']) : APP;
			$options['mode'] = !empty($this->params['mode']) ? $this->params['mode'] : 'interactive';
			$options['files'] = !empty($this->params['files']) ? explode(',',$this->params['files']) : array('php');
			$this->{'Code'.ucfirst($this->args[0])}->execute($options);
		} else {
			$this->out('Usage: cake code task [options]');
			$this->out('');
			$this->out('Tasks:');
			$this->out('convention : checks code for CakePHP conventions');
			$this->out('whitespace : checks files for leading and trailing whitespace');
			$this->out('');
			$this->out('Options:');
			$this->out('-path : any path constant or string, defaults to APP');
			$this->out('-mode : interactive (default) shows errors individually and prompts for change ');
			$this->out('        diff                  writes a unified diff to current directory, does not change any files');
			$this->out('        silent                corrects all errors without prompting');
			$this->out('-files : comma-delimited extension list, defaults to php');
		}
	}

}
?>