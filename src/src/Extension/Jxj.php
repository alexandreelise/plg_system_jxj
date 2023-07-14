<?php
declare(strict_types=1);

/**
 * System plugin to make 2 Joomla! website communicate together via webservices.
 *
 * @copyright (c) 2009 - present. Mr Alexandre J-S William ELISÉ. All rights reserved.
 * @license GNU Affero General Public License v3.0 or later (AGPL-3.0-or-later)
 */

namespace AlexApi\Plugin\System\Jxj\Extension;

use AlexApi\Plugin\System\Jxj\Helper\JxjHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Event\SubscriberInterface;

defined('_JEXEC') or die;


/**
 *
 *
 * Class PlgSystemJxj
 */
final class Jxj extends CMSPlugin implements
    SubscriberInterface,
    ContainerAwareInterface,
    CurrentUserInterface
{
    use ContainerAwareTrait;
    use CurrentUserTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    bool
     * @since  3.7.0
     */
    protected $autoloadLanguage = true;

    /**
     * Convenient helper class to extract functionality
     * and hopefully make it easier to test
     *
     * @var JxjHelper
     */
    private $helper;

    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialise' => 'onAfterInitialise',
            'onContentAfterSave' => 'onContentAfterSave',
            'onAfterDelete' => 'onAfterDelete',
            'onContentChangeState' => 'onContentChangeState',
        ];
    }

    public function onAfterInitialise() 
    {
       $this->helper = $this->getContainer()->get(JxjHelper::class);  
    }


    public function onContentAfterSave(Event $event): bool
    {

        /**
         * Create the same article on both Joomla! websites onContentAfterSave event
         *
         * @param string $context The context
         * @param Table $item The table
         * @param bool $isNew Is new item
         * @param array $data The validated data
         *
         * @since   3.7.0
         */
        [$context, $item, $isNew, $data] = $event->getArguments();

        // Check if data is an array and the item has an id
        if (!is_array($data) || !property_exists($item, 'id')) {
            return true;
        }

        if ($context === 'com_content.article') {
            $response = $isNew ? $this->helper->addArticle($data) : $this->helper->patchArticle($data, (int)$item->id);
            $this->helper->getLogger()->info(sprintf('%s, %s, %s',__METHOD__, $context, print_r($response, true)), ['category' => 'jxj']);
        }

        return true;
    }

    public function onAfterDelete(Event $event): bool
    {

        [$context, $item] = $event->getArguments();

        // Check if data is an array and the item has an id
        if (empty($item->id ?: null)) {
            return true;
        }

        if ($context === 'com_content.article') {
            $response = $this->helper->deleteArticle([], (int)$item->id);
            $this->helper->getLogger()->info(sprintf('%s, %s, %s',__METHOD__, $context, print_r($response, true)), ['category' => 'jxj']);
        }

        return true;
    }

    public function onContentChangeState(Event $event): bool
    {

        /**
         * @param string $context
         * @param array $pks
         * @param int $value
         */
        [$context, $pks, $value] = $event->getArguments();


        // Check if data is an array and the item has an id
        if (empty($pks)) {
            return true;
        }

        if ($context === 'com_content.article') {
            foreach ($pks as $pk) {
                $response = $this->helper->patchArticle(['state' => $value], (int)$pk);
                $this->helper->getLogger()->info(sprintf('%s, %s, %s',__METHOD__, $context, print_r($response, true)), ['category' => 'jxj']);
            }
        }

        return true;
    }
}

