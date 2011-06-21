<?php
namespace de\RaumZeitLabor\PartKeepr\User;
use de\RaumZeitLabor\PartKeepr\Service\RestfulService;

declare(encoding = 'UTF-8');

use de\RaumZeitLabor\PartKeepr\Service\AdminService;
use de\RaumZeitLabor\PartKeepr\PartKeepr,
	de\RaumZeitLabor\PartKeepr\User\User,
	de\RaumZeitLabor\PartKeepr\Session\SessionManager;

class UserService extends AdminService implements RestfulService {
	
	/**
	 * Implements the get() call for the RestfulService.
	 * 
	 * If the "id" parameter is passed, try to return the user by id. If not,
	 * return a list.
	 * 
	 * @see de\RaumZeitLabor\PartKeepr\Service.RestfulService::get()
	 */
	public function get () {
		if ($this->hasParameter("id")) {
			 return array("data" => UserManager::getInstance()->getUser($this->getParameter("id"))->serialize());
		} else {
			if ($this->hasParameter("sort")) {
				$tmp = json_decode($this->getParameter("sort"), true);
				
				$aSortParams = $tmp[0];
			} else {
				$aSortParams = array(
					"property" => "username",
					"direction" => "ASC");
			}
			return UserManager::getInstance()->getUsers(
			$this->getParameter("start", $this->getParameter("start", 0)),
			$this->getParameter("limit", $this->getParameter("limit", 25)),
			$this->getParameter("sortby", $aSortParams["property"]),
			$this->getParameter("dir", $aSortParams["direction"]),
			$this->getParameter("query", ""));
		}
	}
	
	/**
	 * Creates a new user.
	 * 
	 * @see de\RaumZeitLabor\PartKeepr\Service.RestfulService::create()
	 */
	public function create () {
		$this->requireParameter("username");
		
		$user = new User;
		
		$this->setUserData($user);
		
		UserManager::getInstance()->createUser($user);
		
		return array("data" => $user->serialize());
	}
	
	/**
	 * Sets the data for this user. Used by update() and create().
	 * @param User $user The user object
	 */
	private function setUserData (User $user) {
		$user->setUsername($this->getParameter("username"));
		
		if ($this->hasParameter("password") && $this->getParameter("password") !== "") {
			$user->setHashedPassword($this->getParameter("password"));
		} else {
			$user->setHashedPassword("");
		}
	}
	
	/**
	 * Updates the user informations.
	 * @see de\RaumZeitLabor\PartKeepr\Service.RestfulService::update()
	 */
	public function update () {
		$this->requireParameter("id");
		$this->requireParameter("username");
		$user = UserManager::getInstance()->getUser($this->getParameter("id"));

		$this->setUserData($user);
		PartKeepr::getEM()->flush();
		
		return array("data" => $user->serialize());
		
	}
	
	/**
	 * Deletes the user from the database.
	 * @see de\RaumZeitLabor\PartKeepr\Service.RestfulService::destroy()
	 */
	public function destroy () {
		$this->requireParameter("id");
		
		UserManager::getInstance()->deleteUser($this->getParameter("id"));
		
		return array("data" => null);
	}
}