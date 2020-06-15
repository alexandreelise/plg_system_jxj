<?php
/**
 * Helper class to decouple functionality from the main plugin
 * and hopefully making it easier to test
 *
 * @package       helper
 * @author        Alexandre ELISÉ <contact@alexandre-elise.fr>
 * @link          https://alexandre-elise.fr
 * @copyright (c) 2020 . Alexandre ELISÉ . Tous droits réservés.
 * @license       GPL-2.0-and-later GNU General Public License v2.0 or later
 * Created Date : 13/06/2020
 * Created Time : 09:38
 */

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Log\Log;

defined('_JEXEC') or die;


/**
 *
 * Class J4xconnectorHelper
 */
final class JxjHelper
{
	/**
	 * Parameters coming from the plugin configuration
	 *
	 * @var \Joomla\Registry\Registry $params
	 */
	private $params;
	
	/**
	 * PSR-3 compatible Logger available since Joomla! 3.8
	 *
	 * @var \Joomla\CMS\Log\DelegatingPsrLogger
	 */
	private $logger;
	
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
	 */
	public function getLogger()
	{
		return $this->logger;
	}
	
	/**
	 * Use Http request to create (add) an article using the webservice endpoint (or route)
	 *
	 * @param   array  $current_data
	 *
	 * @return string|boolean
	 */
	public function addArticle(array $current_data)
	{
		$endpoint = '/api/index.php/v1/content/article';
		
		$url = $this->getBasePath() . $endpoint;
		
		$data = $this->processData($current_data);
		
		return $this->postJsonDataUsingStream($url, $data);
	}
	
	/**
	 * The base_path (scheme + domain) of the Joomla! website you want to "communicate" with
	 *
	 * @return string
	 */
	private function getBasePath()
	{
		$base_path = $this->params->get('base_path', '');
		
		return JStringPunycode::urlToUTF8($base_path);
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
	
	
	/**
	 * The default remote category where to create articles into
	 * @return int
	 */
	private function getArticleCategoryId()
	{
		return (int) $this->params->get('category_id');
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
	 * @return false|string
	 */
	private function postJsonDataUsingStream($url, array $data)
	{
		$data_string = json_encode($data);
		
		$result = file_get_contents($url, null, stream_context_create(
			[
				'http' => [
					'method'  => 'POST',
					'header'  => 'Content-Type: application/json' . "\r\n"
						. $this->getHeaderFromWebsiteVersion($this->params->get('website_version', 0))
						. 'Content-Length: ' . strlen($data_string) . "\r\n",
					'content' => $data_string,
				],
				'ssl'  => [
					'verify_peer'       => false,
					'verify_peer_name'  => false,
					'allow_self_signed' => true,
				],
			]));
		
		return $result;
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
					. 'Authorization: Bearer ' . trim($this->getApiToken()) . "\r\n";
			case 0:
			default:
				return '';
		}
	}
	
}
