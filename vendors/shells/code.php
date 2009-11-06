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
			$options['path'] = !empty($this->params['path']) ? constant($this->params['path']) : APP;
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
			$this->out('-path : any constant, default APP');
			$this->out('-mode : (default)interactive diff silent');
			$this->out('-files : comma-delimited extension list, default php');
		}
	}

}
?>