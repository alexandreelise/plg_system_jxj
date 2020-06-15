<?php
/**
 * System plugin to make 2 Joomla! website communicate together via webservices.
 *
 * @package       jxj
 * @author        Alexandre ELISÉ <contact@alexandre-elise.fr>
 * @link          https://alexandre-elise.fr
 * @copyright (c) 2020 . Alexandre ELISÉ . Tous droits réservés.
 * @license       GPL-2.0-and-later GNU General Public License v2.0 or later
 * Created Date : 11/06/2020
 * Created Time : 17:53
 */

use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die;


/**
 *
 *
 * Class PlgSystemJxj
 */
class PlgSystemJxj extends CMSPlugin
{
	/**
	 * The CMS Application
	 * @var \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;
	
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.7.0
	 */
	protected $autoloadLanguage = true;
	
	/**
	 * Convenient helper class to extract functionality
	 * and hopefully make it easier to test
	 *
	 * @var \JxjHelper
	 */
	private $helper;
	
	/**
	 * PlgSystemJxj constructor.
	 *
	 * @param          $subject
	 * @param   array  $config
	 */
	public function __construct(&$subject, $config = [])
	{
		parent::__construct($subject, $config);
		
		JLoader::register('JxjHelper', JPATH_PLUGINS . '/system/jxj/helper/JxjHelper.php');
		
		$this->helper = new JxjHelper($this->params);
		$this->app->setLogger($this->helper->getLogger());
	}
	
	
	/**
	 * Create the same article on both Joomla! websites onContentAfterSave event
	 *
	 * @param   string   $context  The context
	 * @param   JTable   $item     The table
	 * @param   boolean  $isNew    Is new item
	 * @param   array    $data     The validated data
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	public function onContentAfterSave($context, $item, $isNew, $data = [])
	{
		// Check if data is an array and the item has an id
		if (!is_array($data) || empty($item->id))
		{
			return true;
		}
		
		if ($context === 'com_content.article')
		{
			$response = $this->helper->addArticle($data);
			$this->helper->getLogger()->info(__METHOD__ . ' add article response ' . print_r($response, true), ['category' => 'jxj']);
		}
		
		return true;
	}
	
}
