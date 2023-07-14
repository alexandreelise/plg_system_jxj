<?php
declare(strict_types=1);

/**
 * Helper class to decouple functionality from the main plugin
 * and hopefully making it easier to test
 *
 * @copyright (c) 2009 - present. Mr Alexandre J-S William ELISÉ. All rights reserved.
 * @license       GNU Affero General Public License v3.0 or later (AGPL-3.0-or-later)
 */

namespace AlexApi\Plugin\System\Jxj\Helper;

use Composer\CaBundle\CaBundle;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Http\Response;
use Joomla\CMS\Log\Log;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\Registry\Registry;
use Joomla\Utilities\IpHelper;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use function array_key_exists;
use function in_array;
use function is_dir;
use function json_encode;
use function print_r;
use function strlen;
use function strtolower;
use function trim;

defined('_JEXEC') or die;


/**
 *
 * Class J4xconnectorHelper
 */
final class JxjHelper implements LoggerAwareInterface
{
	use LoggerAwareTrait;

	/**
	 * Parameters coming from the plugin configuration
	 *
	 * @var \Joomla\Registry\Registry $params
	 */
	private $params;

	/**
	 * J4xconnectorHelper constructor.
	 *
	 * @param $params
	 */
	public function __construct($params)
	{
		$this->params = $params;
		$this->logger = Log::createDelegatedLogger();
	}

	/**
	 * PSR-3 compatible logger available since Joomla! 3.8
	 * @return \Joomla\CMS\Log\DelegatingPsrLogger
	 *
	 * @since 1.0.0
	 */
	public function getLogger()
	{
		return $this->logger;
	}


	/**
	 * Use Http request to create (add) an article using the webservice endpoint (or route)
	 *
	 * @param   array  $currentData
	 *
	 * @return bool|Response|string
	 *
	 * @since 0.1.0
	 */
	public function addArticle(array $currentData = [])
	{
		$endpoint = '/api/index.php/v1/content/articles';

		$url = sprintf('%s%s', $this->getBasePath(), $endpoint);

		$data = $this->processData($currentData);

		return $this->requestJsonDataUsingStream($url, $data, 'POST');
	}

	/**
	 * Use Http request to get (browse) articles using the webservice endpoint (or route)
	 *
	 * @param   array  $currentData
	 *
	 * @return bool|Response|string
	 */
	public function browseArticle(array $currentData = [], int $pk = 0)
	{
		return $this->getArticle([], 0);
	}

	/**
	 * Use Http request to create (add) an article using the webservice endpoint (or route)
	 *
	 * @param   array  $currentData
	 *
	 * @return bool|Response|string
	 */
	public function getArticle(array $currentData = [], int $pk = 0)
	{
		$endpoint = '/api/index.php/v1/content/articles/' . ($pk ?: '');

		$url = $this->getBasePath() . $endpoint;

		$data = [];
		if (!empty($currentData))
		{
			$data = $this->processData($currentData);
		}

		return $this->requestJsonDataUsingStream($url, $data, 'GET');
	}

	/**
	 * Use Http request to delete (delete) an article using the webservice endpoint (or route)
	 *
	 * @param   array  $currentData
	 *
	 * @return bool|Response|string
	 */
	public function deleteArticle(array $currentData = [], int $pk = 0)
	{
		$endpoint = '/api/index.php/v1/content/articles';

		$url = $this->getBasePath() . $endpoint;

		$data = [];
		if (!empty($currentData))
		{
			$data = $this->processData($currentData);
		}

		return $this->requestJsonDataUsingStream($url, $data, 'DELETE');
	}

	/**
	 * Use Http request to update (edit) an article using the webservice endpoint (or route)
	 *
	 * @param   array  $currentData
	 *
	 * @return bool|Response|string
	 */
	public function patchArticle(array $currentData = [], int $pk = 0): Response|bool|string
	{
		$endpoint = '/api/index.php/v1/content/articles/' . ($pk ?: '');

		$url = $this->getBasePath() . $endpoint;

		$data = $this->processData($currentData);

		return $this->requestJsonDataUsingStream($url, $data, 'PATCH');
	}

	/**
	 * Use Http request to update (edit) whole article using the webservice endpoint (or route)
	 *
	 * @param   array  $currentData
	 *
	 * @return bool|Response|string
	 */
	public function putArticle(array $currentData = [], int $pk = 0)
	{
		$endpoint = '/api/index.php/v1/content/articles/' . ($pk ?: '');

		$url = sprintf('%s%s', $this->getBasePath(), $endpoint);

		$data = $this->processData($currentData);

		return $this->requestJsonDataUsingStream($url, $data, 'PUT');
	}

	/**
	 * The base_path (scheme + domain) of the Joomla! website you want to "communicate" with
	 *
	 * @return string
	 */
	private function getBasePath(): string
	{
		$base_path = $this->params->get('base_path', '');

		return PunycodeHelper::urlToUTF8($base_path);
	}

	/**
	 *  Filtered keys to use when creating an article
	 *
	 * @param   array  $data
	 *
	 * @return array
	 */
	private function processData(array $data)
	{
		if (isset($this->logger))
		{
			$this->getLogger()->info(__METHOD__ . ' data ' . print_r($data, true), ['category' => 'jxj']);
		}

		$article      = [];
		$allowed_keys = [
			'title',
			'alias',
			'articletext',
			'introtext',
			'fulltext',
			'language',
			'images',
			'urls',
			'state',
			'featured',
		];

		foreach ($allowed_keys as $key)
		{
			if (!array_key_exists($key, $data))
			{
				continue;
			}
			$article[$key] = $data[$key];
		}

		$article['catid'] = $this->getArticleCategoryId();

		if (isset($this->logger))
		{
			$this->getLogger()->info(__METHOD__ . ' article ' . print_r($article, true), ['category' => 'jxj']);
		}

		return $article;
	}


	/**
	 * The default remote category where to create articles into
	 * @return int
	 */
	private function getArticleCategoryId()
	{
		return (int) $this->params->get('category_id');
	}

	/**
	 * Send http request using builtin PHP streams
	 * NOTE:
	 * verify_peer => false
	 * verify_peer_name => false
	 * allow_self_signed => true
	 * is INSECURE
	 * Use it only on dev environments
	 *
	 * @param          $url
	 * @param   array  $data
	 *
	 * @return Joomla\CMS\Http\Response
	 */
	private function requestJsonDataUsingStream(string $url, array $data = [], string $method = 'get')
	{
		$allowedMethods = ['get', 'post', 'put', 'patch', 'delete', 'head', 'options'];
		$currentMethod  = strtolower($method);
		if (!in_array($method, $allowedMethods, true))
		{
			throw new BadMethodCallException('This Http method is not allowed', 400);
		}

		$onLocalhost = $this->isLocalhost();

		$dataString = '';
		if (!empty($data))
		{
			$dataString = json_encode($data);
		}


		$opts = [
			'http' => [
				'method'  => $currentMethod,
				'header'  => 'Content-Type: application/json' . "\r\n"
					. $this->getHeaderFromWebsiteVersion($this->params->get('website_version', 0))
					. 'Content-Length: ' . strlen($dataString) . "\r\n",
				'content' => $dataString,
			],
			'ssl'  => [
				'verify_peer'       => !$onLocalhost,
				'verify_peer_name'  => !$onLocalhost,
				'allow_self_signed' => $onLocalhost,
			],
		];

		if ($onLocalhost && ((int) $this->params->get('website_version', 0) === 4))
		{
			$caPathOrFile = CaBundle::getSystemCaRootBundlePath();
			if (is_dir($caPathOrFile))
			{
				$opts['ssl']['capath'] = $caPathOrFile;
			}
			else
			{
				$opts['ssl']['cafile'] = $caPathOrFile;
			}
		}

		$httpClient = HttpFactory::getHttp(new Registry($opts), 'stream');

		return $httpClient->$currentMethod($url, $dataString);
	}

	/**
	 * Try to detect localhost
	 *
	 * @return bool
	 */
	private function isLocalhost(): bool
	{
		// Hardcoded to prevent unwanted external change.
		// May change according to your needs and use cases
		$allowedList = [
			'127.0.0.1',
			'::1',
		];

		return IpHelper::IPinList(IpHelper::getIp(), $allowedList);
	}

	/**
	 * Add HTTP headers corresponding to each Joomla! version or API used
	 * (Quick note: might want to find a better solution to handle this)
	 *
	 * @param $website_version
	 *
	 * @return string
	 */
	private function getHeaderFromWebsiteVersion($website_version)
	{
		$version = (int) $website_version;
		switch ($version)
		{
			case 3:
				return 'Authorization: Bearer ' . trim($this->getApiToken()) . "\r\n";
			case 4:
				return 'Accept: application/vnd.api+json' . "\r\n"
					. 'X-Joomla-Token: ' . trim($this->getApiToken()) . "\r\n";
			case 0:
			default:
				return '';
		}
	}

	/**
	 *  Api token of the webservice we want to "communicate" with
	 * @return string
	 */
	private function getApiToken()
	{
		$api_token = $this->params->get('api_token', '');

		return OutputFilter::cleanText($api_token);
	}

}
