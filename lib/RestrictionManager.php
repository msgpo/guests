<?php
/**
 * @copyright Copyright (c) 2018 Robin Appelman <robin@icewind.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Guests;


use OC\NavigationManager;
use OCP\IRequest;
use OCP\IServerContainer;
use OCP\IUserSession;

class RestrictionManager {
	/** @var AppWhitelist */
	private $whitelist;

	/** @var IRequest */
	private $request;

	/** @var IUserSession */
	private $userSession;

	/** @var IServerContainer */
	private $server;

	/** @var Hooks */
	private $hooks;

	public function __construct(AppWhitelist $whitelist, IRequest $request, IUserSession $userSession, IServerContainer $server, Hooks $hooks) {
		$this->whitelist = $whitelist;
		$this->request = $request;
		$this->userSession = $userSession;
		$this->server = $server;
		$this->hooks = $hooks;
	}

	public function verifyAccess() {
		$this->whitelist->verifyAccess($this->userSession->getUser(), $this->request);
	}

	public function setupRestrictions() {
		\OCP\Util::connectHook('OC_Filesystem', 'preSetup', $this->hooks, 'setupReadonlyFilesystem');

		/** @var NavigationManager $navManager */
		$navManager = $this->server->getNavigationManager();

		\OCP\Util::addStyle('guests', 'personal');

		$this->server->registerService('NavigationManager', function () use ($navManager) {
			return new FilteredNavigationManager($this->userSession->getUser(), $navManager, $this->whitelist);
		});
	}
}