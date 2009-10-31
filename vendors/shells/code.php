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
			if (!empty($this->args[1])) {
				$this->args[1] = constant($this->args[1]);
			} else {
				$this->args[1] = APP;
			}
			$this->{'Code'.ucfirst($this->args[0])}->execute($this->args[1]);
		} else {
			$this->out('Usage: cake code type');
			$this->out('');
			$this->out('type should be space-separated');
			$this->out('list of any combination of:');
			$this->out('');
			$this->out('convention');
			$this->out('whitespace');
		}
	}

}
?>