<?php

/*
 * Copyright (C) 2014 Mihai Chelaru
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

final class GdbcUltraCommunityPublicModule extends GdbcBasePublicModule
{

	protected function __construct()
	{
		parent::__construct();

		if (version_compare(PHP_VERSION, '5.3.0', '<')) {
			return;
		}

		if(!class_exists('\UltraCommunity\UltraCommHooks')) {
			return;
		}

		if($this->getOption(GdbcUltraCommunityAdminModule::OPTION_LOGIN_FORM_PROTECTION_ACTIVATED))
		{
			$this->registerLoginHooks();
		}

		if($this->getOption(GdbcUltraCommunityAdminModule::OPTION_REGISTER_FORM_PROTECTION_ACTIVATED))
		{
			$this->registerRegistrationHooks();
		}

//		if($this->getOption(GdbcUserProAdminModule::OPTION_LOST_PASS_FORM_PROTECTION_ACTIVATED))
//		{
//			$this->registerLostPasswordHooks();
//		}
//
//
//		if($this->getOption(GdbcUserProAdminModule::OPTION_CHANGE_PASS_FORM_PROTECTION_ACTIVATED))
//		{
//			$this->registerChangePasswordHooks();
//		}
	}

	private function registerLoginHooks()
	{
		add_action('uc_action_login_form_bottom', array($this, 'renderTokenFieldIntoForm'));
		add_action('uc_action_before_user_log_in', array($this, 'validateLogin'), 10, 1);

	}


	private function registerRegistrationHooks()
	{
		add_action('uc_action_registration_form_bottom', array($this, 'renderTokenFieldIntoForm'));
		add_filter('uc_action_before_user_registration', array($this, 'validateRegistration'));
	}


	public function validateLogin($userName)
	{

		$this->attemptEntity->SectionId = $this->getOptionIdByOptionName(GdbcUltraCommunityAdminModule::OPTION_LOGIN_FORM_PROTECTION_ACTIVATED);
		$this->attemptEntity->Notes = array('username' => $userName);
		$this->attemptEntity->Notes = array_map( is_email($this->attemptEntity->Notes['username']) ? 'sanitize_email' : 'sanitize_user' , $this->attemptEntity->Notes);

		if(GdbcRequestController::isValid($this->attemptEntity))
			return;

		throw new  Exception(__('Invalid Username or Password!', GoodByeCaptcha::PLUGIN_SLUG));

	}

	public function validateRegistration($userEntity)
	{

		$this->attemptEntity->SectionId = $this->getOptionIdByOptionName(GdbcUltraCommunityAdminModule::OPTION_REGISTER_FORM_PROTECTION_ACTIVATED);

		if(GdbcRequestController::isValid($this->attemptEntity))
			return;

		throw new  Exception(__('We\'ve encountered an error while processing your request!', GoodByeCaptcha::PLUGIN_SLUG));

	}

	/**
	 * @return int
	 */
	protected function getModuleId()
	{
		return GdbcModulesController::getModuleIdByName(GdbcModulesController::MODULE_ULTRA_COMMUNITY);
	}


	public static function getInstance()
	{
		static $publicInstance = null;
		return null !== $publicInstance ? $publicInstance : $publicInstance = new self();
	}

}
