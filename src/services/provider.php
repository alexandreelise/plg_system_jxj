<?php

declare(strict_types=1);

/**
 *
 * @copyright (c) 2019 - present. Mr Alexandre J-S William ELISÉ. All rights reserved.
 * @license       GNU Affero General Public License v3.0 or later
 */

use AlexApi\Plugin\System\Jxj\Extension\Jxj;
use AlexApi\Plugin\System\Jxj\Helper\JxjHelper;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

defined('_JEXEC') || die;

return new class implements ServiceProviderInterface {
    public function register(Container $container)
    {
        $container->set(PluginInterface::class, function (Container $container) {
            $plugin     = PluginHelper::getPlugin('system', 'jxj');
            $dispatcher = $container->get(DispatcherInterface::class);

            // Specific to this plugin
            $subContainer = $container->createChild();

            // Inject subContainer to be able to access injected dependencies inside the plugin
            $jxj = new Jxj($dispatcher, (array) $plugin);

            $subContainer->set(JxjHelper::class, function (Container $container) use ($jxj) {
                $jxjHelper = new JxjHelper($jxj->params);
                Factory::getApplication()->setLogger($jxjHelper->getLogger());

                return $jxjHelper;
            }, false);

            // DI Sub Container specific for this plugin not Global DI Container
            $jxj->setContainer($subContainer);

            $jxj->setApplication(Factory::getApplication());

            return $jxj;
        }, false);
    }

};

