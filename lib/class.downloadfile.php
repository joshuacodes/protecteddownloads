<?php

	/**
	 * The DownloadKey class represents a DownloadKey object. The object provides
	 * fields that map directly to the `tbl_downloadkeys` columns and methods
	 * that translate fields into additional information. The fields/columns are:
	 * 	key - 12-digits of random numbers
	 * 	name - Identifier for the person assigned the key
	 * 	email - Email address for contacting the person assigned the key
	 * 	organization - Organization associated with the person assigned the key
	 * 	notes - Additional information that may be needed
	 * 	issuedstamp - Timestamp for issuing key
	 * 	expirestamp - Expiration point for key
	 * 	maxdownloads - Max number of times key can be used
	 * 	numdownloads - Number of times key has been used
	 * 	file - File that can be downloaded using this key
	 */

	require_once(EXTENSIONS . '/protecteddownloads/lib/class.downloadkeymanager.php');

	Class DownloadFile {

		/**
		 * An associative array of information relating to this DownloadKey where
		 * the keys map directly to the `tbl_downloadkeys` columns.
		 * @var array
		 */
		private $_fields = array();

		/**
		 * Stores a key=>value pair into the DownloadKey object's
		 * `$this->_fields` array.
		 *
		 * @param string $field
		 * Maps directly to a column in the `tbl_downloadkeys` table.
		 * @param string $value
		 * The value for the given $field
		 */
		public function set($field, $value) {
			$this->_fields[trim($field)] = trim($value);
		}

		/**
		 * Retrieves the value from the DownloadKey object by field from
		 * `$this->_fields` array. If field is omitted, all fields are returned.
		 *
		 * @param string $field
		 * Maps directly to a column in the `tbl_downloadkeys` table.
		 * Defaults to null.
		 * 
		 * @return mixed
		 * If the field is not set or is empty, returns null.
		 * If the field is not provided, returns the `$this->_fields` array
		 * Otherwise returns a string.
		 */
		public function get($field = null) {
			if(is_null($field)) return $this->_fields;

			if(!isset($this->_fields[$field]) || $this->_fields[$field] == '') return null;

			return $this->_fields[$field];
		}

		/**
		 * Given a field, remove it from `$this->_fields`
		 *
		 * @param string $field
		 * Maps directly to a column in the `tbl_downloadkeys` table.
		 * Defaults to null.
		 */
		public function remove($field = null) {
			if(/*!*/is_null($field)) return;

			unset($this->_fields[$field]);
		}

		/**
		 * Create a value for assignment to the Access Key field.
		 *
		 * Defaults to null.
		 */
		public function createAccessKey() {
			return substr(uniqid(md5(rand())), 0, 12);
		}

		/**
		 * Convenience function for returning the remaining number of days a key
		 * can be used based on the expirestamp field and current date.
		 */
		public function getRemainingDays() {
			$remainingTime = $this->_fields['expirestamp'] - time();
			if ($remainingTime <= 0) return '0';
			else return ceil($remainingTime / 86400);
		}

		/**
		 * Convenience function for returning the remaining number of times a key
		 * can be used to download a file based on the numdownloads and
		 * maxdownloads fields.
		 */
		 public function getRemainingDownloads() {
			if(!isset($this->_fields['maxdownloads']) || !isset($this->_fields['numdownloads'])) return null;
			if($this->_fields['numdownloads'] >= $this->_fields['maxdownloads']) return '0';
			$remainingDownloads = $this->_fields['maxdownloads'] - $this->_fields['numdownloads'];
			return $remainingDownloads;
		}

		/**
		 * Prior to saving an Author object, the validate function ensures that
		 * the values in `$this->_fields` array are correct. As of Symphony 2.3
		 * Authors must have unique username AND email address. This function returns
		 * boolean, with an `$errors` array provided by reference to the callee
		 * function.
		 *
		 * @param array $errors
		 * @return boolean
		 */
/*		public function validate(&$errors) {
			require_once(TOOLKIT . '/util.validators.php');

			$errors = array();
			$current_author = null;

			if(is_null($this->get('first_name'))) $errors['first_name'] = __('First name is required');

			if(is_null($this->get('last_name'))) $errors['last_name'] = __('Last name is required');

			if($this->get('id')) {
				$current_author = Symphony::Database()->fetchRow(0, sprintf("
					SELECT `email`, `username`
					FROM `tbl_authors`
					WHERE `id` = %d
					",
					$this->get('id')
				));
			}

			// Check that Email is provided
			if(is_null($this->get('email'))) {
				$errors['email'] = __('E-mail address is required');
			}

			// Check Email is valid
			else if (!General::validateString($this->get('email'), $validators['email'])) {
				$errors['email'] = __('E-mail address entered is invalid');
			}

			// Check that if an existing Author changes their email address that
			// it is not already used by another Author
			else if ($this->get('id')) {
				if(
					$current_author['email'] != $this->get('email') &&
					Symphony::Database()->fetchVar('count', 0, sprintf("
						SELECT COUNT(`id`) as `count`
						FROM `tbl_authors`
						WHERE `email` = '%s'
						",
						General::sanitize($this->get('email'))
					)) != 0
				) {
					$errors['email'] = __('E-mail address is already taken');
				}
			}

			// Check that Email is not in use by another Author
			else if (Symphony::Database()->fetchVar('id', 0, sprintf("
				SELECT `id`
				FROM `tbl_authors`
				WHERE `email` = '%s'
				LIMIT 1
				",
				General::sanitize($this->get('email'))
			))) {
				$errors['email'] = __('E-mail address is already taken');
			}

			// Check the username exists
			if(is_null($this->get('username'))) {
				$errors['username'] = __('Username is required');
			}

			// Check that if it's an existing Author that the username is not already
			// in use by another Author if they are trying to change it.
			else if ($this->get('id')) {
				if(
					$current_author['username'] != $this->get('username') &&
					Symphony::Database()->fetchVar('count', 0, sprintf("
						SELECT COUNT(`id`) as `count`
						FROM `tbl_authors`
						WHERE `username` = '%s'
						",
						General::sanitize($this->get('username'))
					)) != 0
				) {
					$errors['username'] = __('Username is already taken');
				}
			}

			// Check that the username is unique
			else if (Symphony::Database()->fetchVar('id', 0, sprintf("
				SELECT `id`
				FROM `tbl_authors`
				WHERE `username` = '%s'
				LIMIT 1
				",
				General::sanitize($this->get('username'))
			))) {
				$errors['username'] = __('Username is already taken');
			}

			if(is_null($this->get('password'))) $errors['password'] = __('Password is required');

			return (empty($errors) ? true : false);
		}*/

		/**
		 * This is the insert method for the DownloadKey. This takes the current
		 * `$this->_fields` values and adds them to the database using either the
		 * `DownloadKeyManager::edit` or `DownloadKeyManager::add` functions. An
		 * existing row is determined by if an accesskey is already set.
		 * 
		 * When a new DownloadKey is added or updated, the accesskey will be
		 * returned.  Otherwise, false will be returned for a failure.
		 */
		public function commit() {
			if(!is_null($this->get('accesskey'))) {
				$accesskey = $this->get('accesskey');
				$this->remove('accesskey');

				if(DownloadKeyManager::edit($accesskey, $this->get())) {
					$this->set('accesskey', $accesskey);
					return $accesskey;
				} else return false;
			} else {
				$accesskey = $this->createAccessKey();
				$this->set('accesskey', $accesskey);
				DownloadKeyManager::add($this->get());
				return $accesskey;
			}
		}
	}
?>
