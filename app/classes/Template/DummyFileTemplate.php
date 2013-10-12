<?php

namespace HQ\Template;

use Nette,
	Nette\Caching;

class DummyFileTemplate extends FileTemplate
{

	private $newSource;

    public function render()
    {
    	$this->newSource = preg_replace_callback('(\$[\w\[\]\(\)\'\-\>]*)', function($matches){
			if (
				strpos($matches[0], 'style') !== FALSE
				|| strpos($matches[0], '$presenter') !== FALSE
			) {
				return $matches[0];
			}
			return "'" . str_replace(
				array(
					"'",
				),
				array(
					'"',
				),
				$matches[0]
			) . "'";
		}, parent::getSource());

		$this->newSource = preg_replace_callback('/\{[^}]*\}/', function($matches){
			if (
				strpos($matches[0], '{if') !== FALSE
				|| strpos($matches[0], '/if}') !== FALSE
				|| strpos($matches[0], '{ifset') !== FALSE
				|| strpos($matches[0], '/ifset}') !== FALSE
				|| strpos($matches[0], '{for') !== FALSE
				|| strpos($matches[0], '/for}') !== FALSE
				|| strpos($matches[0], '/foreach}') !== FALSE
				|| strpos($matches[0], '{else') !== FALSE
				|| strpos($matches[0], '{var') !== FALSE
				|| strpos($matches[0], '|round') !== FALSE
				|| strpos($matches[0], 'round(') !== FALSE
				|| strpos($matches[0], 'strtolower') !== FALSE
				|| strpos($matches[0], 'nl2br') !== FALSE
				|| strpos($matches[0], '{?') !== FALSE
				|| strpos($matches[0], 'count(') !== FALSE
				|| strpos($matches[0], '|count') !== FALSE
				|| strpos($matches[0], '|replace') !== FALSE
				|| strpos($matches[0], 'replace(') !== FALSE
				|| strpos($matches[0], '_replace') !== FALSE
				|| strpos($matches[0], '{sep') !== FALSE
				|| strpos($matches[0], '/sep}') !== FALSE
				|| strpos($matches[0], '{last') !== FALSE
				|| strpos($matches[0], '/last}') !== FALSE
				|| strpos($matches[0], '{first') !== FALSE
				|| strpos($matches[0], '/first}') !== FALSE
				|| strpos($matches[0], '{*') !== FALSE
				|| strpos($matches[0], '*}') !== FALSE
			) {
				return '';
			} elseif (
				strpos($matches[0], 'number') !== FALSE
				|| strpos($matches[0], '|ceil') !== FALSE
				|| strpos($matches[0], 'ceil(') !== FALSE
			) {
				return "{'NUMBER'}";
			} elseif (strpos($matches[0], '{control') !== FALSE) {
				return "{'control'}";
			} elseif (
				strpos($matches[0], '|ucwords') !== FALSE
				|| strpos($matches[0], 'ucwords(') !== FALSE
			) {
				return "{'NAME'}";
			} elseif (
				strpos($matches[0], '|date') !== FALSE
				|| strpos($matches[0], 'date(') !== FALSE
				|| strpos($matches[0], 'format(') !== FALSE
			) {
				return "{'DATE'}";
			} elseif (strpos($matches[0], '?') !== FALSE) {
				return str_replace(array(':', '?'), '.' , $matches[0]);
			}
			return $matches[0];
		}, $this->newSource);

		$this->newSource = str_replace(array(
				"n:",
				"[''",
				"{*",
				"*}",
				"ucwords",
				"{('",
				"')}",
				")' }",
				"', ('"
			),
			array(
				"",
				"[",
				'',
				'',
				'',
				"{'",
				"'}",
				"') }",
				"', '",
			),
			$this->newSource
		);

		// Uncomment for debugging code
    	//die($this->newSource);
        parent::render();
    }

    public function getSource()
    {
    	return $this->newSource;
    }

}
