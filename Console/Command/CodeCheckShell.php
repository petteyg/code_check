<?php

App::uses('Shell', 'Console');

class CodeCheckShell extends Shell {

	public $tasks = array(
		'CodeCheck.CodeCheck',
		'CodeCheck.Convention',
		'CodeCheck.Whitespace',
	);

	public function main()  {
		if (empty($this->args)) {
			return $this->out($this->getOptionParser()->help());
		}
	}

	public function getOptionParser() {
		$this->stdout->styles('option', array('bold' => true));
		$parser = parent::getOptionParser();
		$options = array(
			'opt1' => array(),
			'opt2' => array(),
		);
		$commands = array(
			'convention' => array('help' => 'Tests code for CakePHP conventions'),
			'whitespace' => array('help' => 'Checks for unneccesary and potentially harmful whitespace'),
		);
		$parser->addOptions($options);
		$parser->addSubcommands($commands);
		$parser->description('<info>Ensure code is error-free and follows conventions</info>');
		return $parser;
	}

}
