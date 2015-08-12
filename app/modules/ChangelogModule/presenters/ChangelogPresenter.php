<?php

namespace ChangelogModule;

/**
 * @author Josef Nevoral <josef.nevoral@gmail.com>
 */
class ChangelogPresenter extends \SecuredPresenter
{

	/** @var \HQ\Model\Entity\ChangelogEntity @autowire */
	protected $changelogEntity;

	/** @var  DbChangelog @autowire */
	protected $dbChangelogService;


	public function actionDefault()
	{
		$this->template->errors = array();
		$this->dbChangelogService->importNewChangelogData();
	}

	public function handleExecuteQueries()
	{
		$queriesToExecute = $this->changelogEntity->getTable()
			->where('executed', 0)
			->order('id');
		$errors = $this->dbChangelogService->executeQueries($queriesToExecute);
		if (empty($errors)) {
			$this->flashMessage('All queries has been executed successfully', 'success');
			$this->redirect('Changelog:');
		}

		$this->template->errors = $errors;
	}

	public function renderDefault()
	{
		$this->template->queriesToExecute = $this->changelogEntity->getTable()->where('executed', 0);
	}

	public function createComponentAddToChangelog()
	{
		$form = new \Nette\Application\UI\Form;
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
		$this->dbChangelogService->addNewQueries($values['description'], $values['queries']);

		$this->flashMessage('Queries saved');
		$this->redirect('add');
	}

}