<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/4.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Routers\Media;

trait UrlCompletion
{
	/**
	 * Complete non-absolute, non-localized url by route instance reverse info.
	 * If there is key `mediaSiteVersion` in `$params`, unset this param before
	 * route url completing and choose by this param url prefix to prepend 
	 * into completed url string.
	 * Example:
	 *	Input (`\MvcCore\Route::$reverse`):
	 *		`"/products-list/<name>/<color>"`
	 *	Input ($params):
	 *		`array(
	 *			"name"			=> "cool-product-name",
	 *			"color"			=> "red",
	 *			"variant"		=> array("L", "XL"),
	 *			"mediaSiteVersion"	=> "mobile",
	 *		);`
	 *	Output:
	 *		`/application/base-bath/m/products-list/cool-product-name/blue?variant[]=L&amp;variant[]=XL"`
	 * @param \MvcCore\Route|\MvcCore\Interfaces\IRoute &$route
	 * @param array $params
	 * @throws \InvalidArgumentException
	 * @return string
	 */
	public function UrlByRoute (\MvcCore\Interfaces\IRoute & $route, & $params = []) {
		/** @var $route \MvcCore\Route */
		$requestedUrlParams = $this->GetRequestedUrlParams();
		$mediaSiteVersion = NULL;
		$mediaVersionUrlParam = static::MEDIA_VERSION_URL_PARAM;
		if (isset($params[$mediaVersionUrlParam])) {
			$mediaSiteVersion = $params[$mediaVersionUrlParam];
			unset($params[$mediaVersionUrlParam]);
		} else if (isset($requestedUrlParams[$mediaVersionUrlParam])) {
			$mediaSiteVersion = $requestedUrlParams[$mediaVersionUrlParam];
			unset($requestedUrlParams[$mediaVersionUrlParam]);
		} else {
			$mediaSiteVersion = $this->mediaSiteVersion;
		}
		if ($this->stricModeBySession && $mediaSiteVersion !== $this->mediaSiteVersion) {
			$sessStrictModeSwitchUrlParam = static::SWITCH_MEDIA_VERSION_URL_PARAM;
			$params[$sessStrictModeSwitchUrlParam] = $mediaSiteVersion;
		}
		$routeUrl = $route->Url(
			$params, $requestedUrlParams, $this->getQueryStringParamsSepatator()
		);
		$mediaSiteUrlPrefix = '';
		if ($mediaSiteVersion) {
			if (isset($this->allowedSiteKeysAndUrlPrefixes[$mediaSiteVersion])) {
				$mediaSiteUrlPrefix = $this->allowedSiteKeysAndUrlPrefixes[$mediaSiteVersion];
			} else {
				throw new \InvalidArgumentException(
					'['.__CLASS__.'] Not allowed media site version used to generate url: `'
					.$mediaSiteVersion.'`. Allowed values: `'
					.implode('`, `', array_keys($this->allowedSiteKeysAndUrlPrefixes)) . '`.'
				);
			}
		}
		return $this->request->GetBasePath() 
			. $mediaSiteUrlPrefix 
			. $routeUrl;
	}
}