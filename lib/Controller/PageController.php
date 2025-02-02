<?php
/**
 * @copyright Copyright (c) 2018 John Molakvoæ <skjnldsv@protonmail.com>
 *
 * @author John Molakvoæ <skjnldsv@protonmail.com>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Contacts\Controller;

use OCA\Contacts\AppInfo\Application;
use OCA\Contacts\Service\SocialApiService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IInitialStateService;
use OCP\IRequest;
use OCP\L10N\IFactory;
use OCP\Util;

class PageController extends Controller {
	/** @var IConfig */
	private $config;

	/** @var IInitialStateService */
	private $initialStateService;

	/** @var IFactory */
	private $languageFactory;

	/** @var SocialApiService */
	private $socialApiService;

	public function __construct(IRequest $request,
								IConfig $config,
								IInitialStateService $initialStateService,
								IFactory $languageFactory,
								SocialApiService $socialApiService) {
		parent::__construct(Application::APP_ID, $request);

		$this->config = $config;
		$this->initialStateService = $initialStateService;
		$this->languageFactory = $languageFactory;
		$this->socialApiService = $socialApiService;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * Default routing
	 */
	public function index(): TemplateResponse {
		$locales = $this->languageFactory->findAvailableLocales();
		$defaultProfile = $this->config->getAppValue(Application::APP_ID, 'defaultProfile', 'HOME');
		$supportedNetworks = $this->socialApiService->getSupportedNetworks();

		$this->initialStateService->provideInitialState(Application::APP_ID, 'locales', $locales);
		$this->initialStateService->provideInitialState(Application::APP_ID, 'defaultProfile', $defaultProfile);
		$this->initialStateService->provideInitialState(Application::APP_ID, 'supportedNetworks', $supportedNetworks);

		Util::addScript(Application::APP_ID, 'contacts');
		Util::addStyle(Application::APP_ID, 'contacts');

		return new TemplateResponse(Application::APP_ID, 'main');
	}
}
