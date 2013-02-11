<?php

	Class extension_protecteddownloads extends Extension {

		/**
		 * Add a backend menu items.
		 */
		public function fetchNavigation() {
			return array(
				array(
					'name' => 'Protected Downloads',
					'limit' => 'developer',
					'index' => '300',
					'children' => array(
						array(
							'name'	=> 'Manage Keys',
							'link'	=> '/managekeys/',
							'limit'	=> 'developer'
						),
						array(
							'name'	=> 'Sample Code',
							'link'	=> '/samplecode/',
							'limit'	=> 'developer'
						)
					)
				)
			);
		}

		/**
		 * About this extension (deprecated, remove in future versions):
		 */
		public function about() {
			return array(
				'name' => 'Protected Downloads',
				'version' => '0.1',
				'release-date' => '2013-02-01',
				'author' => array(
					'name' => 'JoshuaCodes',
					'website' => 'http://www.joshuacodes.com/protecteddownloads',
					'email' => 'joshua@joshuacodes.com'),
				'description' => 'Enables restricted access to files.'
			);
		}

		/**
		 * Set the delegates:
		 */
		public function getSubscribedDelegates() {
			return array(
				array(
					'delegate' => 'AddCustomPreferenceFieldsets',
					'page' => '/system/preferences/',
					'callback' => 'appendPreferences'
				),
				array(
					'page' => '/system/preferences/',
					'delegate' => 'Save',
					'callback' => 'savePreferences'
				)
			);
		}

		/**
		 * Append preferences to the preferences page
		 * NOT WORKING YET
		 */
		public function appendPreferences($context) {
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', __('Protected Downloads')));

			$label = Widget::Label(__('Path to Files on Server'));

			$locations = implode("\n", $this->getLocations());

			$label->appendChild(Widget::Textarea('protected_downloads[file_locations]', 5, 50, $locations));

			$group->appendChild($label);

			$group->appendChild(new XMLElement(
				'p',
				__('Relative from the root. Single path per line. Add * at end for wild card matching.'),
				array('class' => 'help')
			));

			$context['wrapper']->appendChild($group);
		}

		/**
		 * Return array of approved file locations
		 * NOT WORKING YET
		 */
		public function getLocations() {
			$locations = unserialize(Symphony::Configuration()->get('file_locations', 'protected_downloads'));
			if(is_array($locations)) {
				return array_filter($locations);
			} else {
				return array();
			}
		}

		/**
		 * Save the preferences
		 * NOT WORKING YET
		 */
		public function savePreferences($context) {
			if(isset($_POST['protected_downloads']['file_locations'])) {
				Symphony::Configuration()->set('file_locations', serialize(explode("\n", str_replace("\r", '', $_POST['protected_downloads']['file_locations']))), 'force_download');
				Symphony::Configuration()->write();
			}
		}

		/**
		 * Installation Tasks
		 * Creates the table for downloads keys in the Symphony database.
		 */
		public function install() {
			// Download Keys Table:
			Symphony::Database()->query("CREATE TABLE IF NOT EXISTS `tbl_downloadkeys` (
				`accesskey` varchar(12) NOT NULL default '',
				`name` varchar(255) NOT NULL default '',
				`email` varchar(255) NOT NULL default '',
				`organization` varchar(255) NOT NULL default '',
				`notes` varchar(255) NOT NULL default '',
				`issuedstamp` INT UNSIGNED,
				`expirestamp` INT UNSIGNED,
				`maxdownloads` SMALLINT UNSIGNED,
				`numdownloads` SMALLINT UNSIGNED default '0',
				`file` varchar(60) NOT NULL default '',
				PRIMARY KEY (accesskey)
			);");
			
			Symphony::Database()->query("CREATE TABLE IF NOT EXISTS `tbl_downloadfiles` (
				`id` INT UNSIGNED NOT NULL default '',
				`filename` varchar(255) NOT NULL default '',
				`extension` varchar(255) NOT NULL default '',
				`path` varchar(255) NOT NULL default '',
				PRIMARY KEY (id)
			);");
		}

		/**
		 * Uninstallation Tasks
		 * Removes the table that was created during installation.
		 */
		public function uninstall() {
			// Drop Protected Download Table:
			Symphony::Database()->query("DROP TABLE `tbl_downloadkeys`");
			Symphony::Database()->query("DROP TABLE `tbl_downloadfiles`");
		}
	}
?>
