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
			'path' => array(
				'short' => 'p',
				'default' => 'APP',
				'help' => 'comma-delimited path list (constants or strings)'),
			'exclude' => array(
				'short' => 'e',
				'default' => NULL,
				'help' => 'comma-delimited path exclusion list (constants or strings)'),
			'mode' => array(
				'short' => 'm',
				'default' => 'interactive',
				'choices' => array('interactive', 'diff', 'silent'),
				'help' => "<option>interactive</option> shows errors individually and prompts for change\n" .
				"<option>diff</option> writes a unified diff to current directory without changing files\n" .
				"<option>silent</option> corrects all errors without prompting\n"),
			'files' => array(
				'short' => 'f',
				'default' => 'php',
				'help' => 'comma-delimited extension list, defaults to php'),
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
