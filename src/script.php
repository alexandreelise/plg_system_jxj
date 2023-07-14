<?php

declare(strict_types=1);

/**
 *
 * @copyright (c) 2019 - present. Mr Alexandre J-S William ELISÉ. All rights reserved.
 * @license       GNU Affero General Public License v3.0 or later
 */

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

defined('_JEXEC') || die;

return new class () implements ServiceProviderInterface {

	/**
	 * @param   Container  $container
	 *
	 *
	 * @since 1.0.0
	 */
	public function register(Container $container)
	{
		$container->set(
			InstallerScriptInterface::class,
			new class ($container->get(AdministratorApplication::class))
				extends InstallerScript
				implements InstallerScriptInterface {
				/**
				 * Minimum PHP version to check
				 *
				 * @var    string
				 * @since  0.1.0
				 */
				protected $minimumPhp = '8.1.0';

				/**
				 * Minimum Joomla version to check
				 *
				 * @var    string
				 * @since  0.1.0
				 */
				protected $minimumJoomla = '4.3.0';

				protected $deleteFolders = [
					'/plugins/system/jxj/language',
					'/plugins/system/jxj/services',
					'/plugins/system/jxj/src',
				];

				protected $deleteFiles = [
					'/administrator/language/en-GB/plg_system_jxj.ini',
					'/administrator/language/en-GB/plg_system_jxj.sys.ini',
					'/administrator/language/fr-FR/plg_system_jxj.ini',
					'/administrator/language/fr-FR/plg_system_jxj.sys.ini',
					'/administrator/language/en-GB/en-GB.plg_system_jxj.ini',
					'/administrator/language/en-GB/en-GB.plg_system_jxj.sys.ini',
					'/administrator/language/fr-FR/fr-FR.plg_system_jxj.ini',
					'/administrator/language/fr-FR/fr-FR.plg_system_jxj.sys.ini',
				];

				public function __construct(private AdministratorApplication $app)
				{
				}

				public function preflight($type, $parent): bool
				{
					$this->app->enqueueMessage(
						sprintf(
							'%s %s version: %s',
							ucfirst($type),
							$parent->getManifest()->name,
							$parent->getManifest()->version
						)
					);
					$this->removeFiles();

					return true;
				}


				public function postflight($type, $parent): bool
				{
					$this->app->enqueueMessage(
						sprintf(
							'%s %s version: %s',
							ucfirst($type),
							$parent->getManifest()->name,
							$parent->getManifest()->version
						)
					);

					return true;
				}

				public function install($parent): bool
				{
					$this->app->enqueueMessage(
						sprintf(
							'%s %s version: %s',
							'Install',
							$parent->getManifest()->name,
							$parent->getManifest()->version
						)
					);

					return true;
				}

				public function update($parent): bool
				{
					$this->app->enqueueMessage(
						sprintf(
							'%s %s version: %s',
							'Update',
							$parent->getManifest()->name,
							$parent->getManifest()->version
						)
					);

					return true;
				}

				public function uninstall($parent): bool
				{
					$this->app->enqueueMessage(
						sprintf(
							'%s %s version: %s',
							'Uninstall',
							$parent->getManifest()->name,
							$parent->getManifest()->version
						)
					);

					return true;
				}
			}
		);
	}
};
