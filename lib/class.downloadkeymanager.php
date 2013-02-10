<?php

	/**
	 * The `DownloadKeyManager` class is responsible for managing all
	 * DownloadKey objects. The class borrows heavily from the AuthorManager
	 * class built into Symphony.  DownloadKeys are similar to Authors in that
	 * they are stored in the database in 'tbl_downloadkeys` and this class
	 * implements CRUD methods to add, fetch, edit, and delete table rows. 
	 */

	require_once(EXTENSIONS . '/protecteddownloads/lib/class.downloadkey.php');

	Class DownloadKeyManager {

		/**
		 * An array of all the objects that the Manager is responsible for.
		 * Defaults to an empty array.
		 * @var array
		 */
		protected static $_pool = array();

		/**
		 * Given an associative array of fields, insert them into the database
		 * returning the resulting Author ID if successful, or false if there
		 * was an error
		 *
		 * @param array $fields
		 * Associative array of field names => values for the Author object
		 * @return integer|boolean
		 * Returns an Author ID of the created Author on success, false otherwise.
		 */
		public static function add(array $fields) {
			return Symphony::Database()->insert($fields, 'tbl_downloadkeys');
		}

		/**
		 * Given a Download Key and associative array of fields, update an existing
		 * Download Key row in the `tbl_authors` database table. Returns boolean for success/failure
		 *
		 * @param integer $id
		 * The ID of the Author that should be updated
		 * @param array $fields
		 * Associative array of field names => values for the Author object
		 * This array does need to contain every value for the author object, it
		 * can just be the changed values.
		 * @return boolean
		 */
		public static function edit($accesskey, array $fields) {
			return Symphony::Database()->update($fields, 'tbl_downloadkeys', sprintf(" `accesskey` = '%s'", $accesskey));
		}

		/**
		 * Given an Author ID, delete an Author from Symphony.
		 *
		 * @param integer $id
		 * The ID of the Author that should be deleted
		 * @return boolean
		 */
		public static function delete($accesskey) {
			return Symphony::Database()->delete('tbl_downloadkeys', sprintf(
			" `accesskey` = %d", $accesskey
			));
		}

		/**
		 * The fetch method returns all Authors from Symphony with the option to sort
		 * or limit the output. This method returns an array of Author objects.
		 *
		 * @param string $sortby
		 * The field to sort the authors by, defaults to 'id'
		 * @param string $sortdirection
		 * Available values of ASC (Ascending) or DESC (Descending), which refer to the
		 * sort order for the query. Defaults to ASC (Ascending)
		 * @param integer $limit
		 * The number of rows to return
		 * @param integer $start
		 * The offset start point for limiting, maps to the LIMIT {x}, {y} MySQL functionality
		 * @param string $where
		 * Any custom WHERE clauses. The `tbl_authors` alias is `a`
		 * @param string $joins
		 * Any custom JOIN's
		 * @return array
		 * An array of Author objects. If no Authors are found, an empty array is returned.
		 */
		public static function fetch($sortby = 'issuedstamp', $sortdirection = 'ASC', $limit = null, $start = null, $where = null, $joins = null) {

			$records = Symphony::Database()->fetch(sprintf("
				SELECT a.*
				FROM `tbl_downloadkeys` AS `a`
				%s
				WHERE %s
				ORDER BY %s %s
				%s %s
				",
				$joins,
				($where) ? $where : 1,
				'a.'.$sortby,
				$sortdirection,
				($limit) ? "LIMIT " . $limit : '',
				($start && $limit) ? ', ' . $start : ''
			));

			if(!is_array($records) || empty($records)) return array();

			$downloadkeys = array();

			foreach($records as $row){
				$downloadkey = new DownloadKey;

				foreach($row as $field => $val) {
					$downloadkey->set($field, $val);
				}

				self::$_pool[$downloadkey->get('accesskey')] = $downloadkey;
				$downloadkeys[] = $downloadkey;
			}

			return $downloadkeys;
		}

		/**
		 * Returns Author's that match the provided ID's with the option to
		 * sort or limit the output. This function will search the
		 * `AuthorManager::$_pool` for Authors first before querying `tbl_authors`
		 *
		 * @param integer|array $id
		 * A single ID or an array of ID's
		 * @return mixed
		 * If `$id` is an integer, the result will be an Author object,
		 * otherwise an array of Author objects will be returned. If no
		 * Authors are found, or no `$id` is given, `null` is returned.
		 */
		public static function fetchByKey($accesskey) {
			$return_single = false;

			if(is_null($accesskey)) return null;

			if(!is_array($accesskey)){
				$return_single = true;
				$accesskey = array($accesskey);
			}

			if(empty($accesskey)) return null;

			$downloadkeys = array();
			$pooled_keys = array();

			// Get all the Author ID's that are already in `self::$_pool`
			$pooled_keys = array_intersect($accesskey, array_keys(self::$_pool));
			foreach($pooled_keys as $pool_key) {
				$downloadkeys[] = self::$_pool[$pool_key];
			}

			// Get all the Author ID's that are not already stored in `self::$_pool`
			$accesskey = array_diff($accesskey, array_keys(self::$_pool));
			$accesskey = array_filter($accesskey);

			if(empty($accesskey)) return ($return_single ? $downloadkeys[0] : $downloadkeys);

			$records = Symphony::Database()->fetch(sprintf("
				SELECT *
				FROM `tbl_downloadkeys`
				WHERE `accesskey` IN ('%s')
				",
				implode(",", $accesskey)
			));

			if(!is_array($records) || empty($records)) return ($return_single ? $downloadkeys[0] : $downloadkeys);

			foreach($records as $row){
				$downloadkey = new DownloadKey;

				foreach($row as $field => $val) {
					$downloadkey->set($field, $val);
				}
				self::$_pool[$downloadkey->get('accesskey')] = $downloadkey;
				$downloadkeys[] = $downloadkey;
			}

			return ($return_single ? $downloadkeys[0] : $downloadkeys);
		}
	}
?>
