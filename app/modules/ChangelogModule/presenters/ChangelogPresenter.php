<?php

namespace ChangelogModule;

/**
 * @author Josef Nevoral <josef.nevoral@gmail.com>
 */
class ChangelogPresenter extends \SecuredPresenter {

	public function actionDefault()
	{
		$this->template->errors = array();
	}

	public function handleExecuteQueries()
	{
		$queriesToExecute = $this->models->changelog->getTable()
			->where('executed', 0)
			->order('id');
		$errors = $this->context->dbChangelog->executeQueries($queriesToExecute);
		if (empty($errors)) {
			$this->flashMessage('All queries has been executed successfully', 'success');
			$this->redirect('Changelog:');
		}

		$this->template->errors = $errors;
	}

	public function renderDefault()
	{
		$this->template->queriesToExecute = $this->models->changelog->getTable()->where('executed', 0);
	}

	public function createComponentAddToChangelog()
	{
		$form = new \Forms\BaseForm;
		$form->addText('description', 'Short description')
			->setRequired('Write short description what you are changing');
		$form->addTextArea('queries', 'SQL queries', 60, 20)
			->setRequired('Huh?')
			->getControlPrototype()->class('long');
		$form->addSubmit('send', 'Save')
			->getControlPrototype()->class('btn btn-primary')->style("padding: 4px 25px;");
		$form->onSuccess[] = callback($this, 'addToChangelog');
		return $form;
	}

	public function addToChangelog($form)
	{
		$values = $form->getValues();
		$this->context->dbChangelog->addNewQueries($values['description'], $values['queries']);

		$this->flashMessage('Queries saved');
		$this->redirect('add');
	}

}