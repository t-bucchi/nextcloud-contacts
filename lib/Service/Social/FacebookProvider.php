<?php
/**
 * @copyright Copyright (c) 2020 Matthias Heinisch <nextcloud@matthiasheinisch.de>
 *
 * @author Matthias Heinisch <nextcloud@matthiasheinisch.de>
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

namespace OCA\Contacts\Service\Social;

use OCP\Http\Client\IClientService;

class FacebookProvider implements ISocialProvider {

	/** @var IClientService */
	private $httpClient;

	public function __construct(IClientService $httpClient) {
		$this->httpClient = $httpClient->NewClient();
	}
	
	/**
	 * Returns the profile-id
	 *
	 * @param {string} the value from the contact's x-socialprofile
	 *
	 * @return string
	 */
	public function cleanupId(string $candidate):string {
		$candidate = basename($candidate);
		if (!is_numeric($candidate)) {
			$candidate = $this->findFacebookId($candidate);
		}
		return $candidate;
	}

	/**
	 * Returns the profile-picture url
	 *
	 * @param {string} profileId the profile-id
	 *
	 * @return string
	 */
	public function getImageUrl(string $profileId):string {
		$recipe = 'https://graph.facebook.com/{socialId}/picture?width=720';
		$connector = str_replace("{socialId}", $profileId, $recipe);
		return $connector;
	}

	/**
	 * Tries to get the facebook id from facebook profile name
	 * e. g. "zuck" --> "4"
	 * Fallback: return profile name
	 * (will give oauth error from facebook except if profile is public)
	 *
	 * @param {string} profileName the user's profile name
	 *
	 * @return string
	 */
	protected function findFacebookId(string $profileName):string {
		try {
			$result = $this->httpClient->get("https://facebook.com/".$profileName);
			if ($result->getStatusCode() !== 200) {
				return $profileName;
			}
			$htmlResult = new \DOMDocument();
			$htmlResult->loadHTML($result->getBody());
			$metas = $htmlResult->getElementsByTagName('meta');
			foreach ($metas as $meta) {
				foreach ($meta->attributes as $attr) {
					$value = $attr->nodeValue;
					if (strpos($value, "/profile/")) {
						$value = str_replace('fb://profile/', '', $value);
						return($value);
					}
				}
			}
			// keyword not found - page changed?
			return $profileName;
		} catch (\Exception $e) {
			return $profileName;
		}
	}
}
