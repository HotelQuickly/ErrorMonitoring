<?php

use Nette\Security\User,
	HotelQuickly as HQ;

class SecuredPresenter extends BasePresenter
{
	public function startup(){
		parent::startup();

		$user = $this->getUser();
		$backlink = $this->storeRequest();

		if(!$this->productionMode && ($this->name == "Changelog:Changelog" || $this->name == 'Secured') ){
			// User can run changelog
		} else {
			// Ckecks if user is logged in, if not, redirect him to log in page
			if (!$user->isLoggedIn() || !$user->isALlowed("Admin:Homepage", 'view') ) {
				if ($user->getLogoutReason() === User::INACTIVITY) {
					$this->flashMessage('You have been logged out due to long inactivity.', 'warning');
				}
				// Fix for new ACL
				$this->user->logout(TRUE);
				$this->redirect(':Login:', array('backlink' => $backlink));
				$this->terminate();
			}

			// Check for access to current presenter && view, else throw exception
			$manageActionsArray = array("edit", "delete", "add");

			$requiredPrivilege = "view";
			foreach($manageActionsArray as $action ){
				if(strpos($this->getAction(), $action) !== FALSE){
					$requiredPrivilege = "manage";
				}
			}
			if (!$user->isAllowed($this->getName(), $requiredPrivilege)) {
				$this->logger->logUnauthorizedAccess();
				throw new HQ\UnauthorizedAccessException("Sorry, you are not authorized to enter this site.");
			}
		}
	}
}
