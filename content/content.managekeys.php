<?php

	/**
	 * Management page for Download Keys in the Protected Downloads extension.
	 * Generate new keys, edit old keys, view/sort existing keys,
	 * and delete keys.
	 */

	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(CONTENT . '/class.sortable.php');
	require_once(CORE . '/class.administration.php');
	require_once(EXTENSIONS . '/protecteddownloads/lib/class.downloadkeymanager.php');

	define_safe('BASE_URL', URL . '/symphony/extension/protecteddownloads/managekeys');

	Class contentExtensionProtectedDownloadsManageKeys extends AdministrationPage {

		public $_DownloadKey;
		public $_errors = array();

		public function sort(&$sort, &$order, $params) {
			if(is_null($sort)) $sort = 'issuedstamp';

			return DownloadKeyManager::fetch($sort, $order);
		}

		public function __viewIndex() {
			$this->setPageType('table');
			$this->setTitle(__('%1$s &ndash; %2$s', array(__('Protected Downloads'), __('Symphony'))));

			$this->appendSubheading(__('View Keys'), Widget::Anchor(
				__('New Key'),
				Administration::instance()->getCurrentPageURL().'new/',
				__('Create a new key'),
				'create button',
				NULL,
				array('accesskey' => 'c')
			));

			Sortable::initialize($this, $downloadkeys, $sort, $order);

			$columns = array(
				array(
					'label' => __('Date Issued'),
					'sortable' => true,
					'handle' => 'issuedstamp'
				),
				array(
					'label' => __('Name'),
					'sortable' => true,
					'handle' => 'name'
				),
				array(
					'label' => __('Download Key'),
					'sortable' => true,
					'handle' => 'accesskey'
				),
				array(
					'label' => __('File'),
					'sortable' => true,
					'handle' => 'file'
				),
				array(
					'label' => __('Remaining Days'),
					'sortable' => true,
					'handle' => 'remainingdays'
				),
				array(
					'label' => __('Remaining Downloads'),
					'sortable' => true,
					'handle' => 'remainingdownloads'
				),
			);

			$aTableHead = Sortable::buildTableHeaders(
				$columns,
				$sort,
				$order,
				(isset($_REQUEST['filter']) ? '&amp;filter=' . $_REQUEST['filter'] : '')
			);

			$aTableBody = array();

			if(!is_array($downloadkeys) || empty($downloadkeys)) {
				$aTableBody = array(Widget::TableRow(array(Widget::TableData(__('None found.'), 'inactive', NULL, count($aTableHead))),	'odd'));
			} else {
				foreach($downloadkeys as $a) {
					// Setup each cell
					$td1 = Widget::TableData(DateTimeObj::format($a->get('issuedstamp'), __SYM_DATETIME_FORMAT__));
					$td2 = Widget::TableData(Widget::Anchor($a->get('name'), 'mailto:'.$a->get('email'), __('Email this person.')));
					$td3 = Widget::TableData(Widget::Anchor($a->get('accesskey'), Administration::instance()->getCurrentPageURL() . 'edit/' . $a->get('accesskey') . '/', __('View details and edit this key.')));
					$td4 = Widget::TableData($a->get('file'));
					$td5 = Widget::TableData($a->getRemainingDays());
					$td6 = Widget::TableData($a->getRemainingDownloads());

					$td3->appendChild(Widget::Input('items['.$a->get('accesskey').']', NULL, 'checkbox'));

					$aTableBody[] = Widget::TableRow(array($td1, $td2, $td3, $td4, $td5, $td6));
				}
			}

			$table = Widget::Table(
				Widget::TableHead($aTableHead),
				NULL,
				Widget::TableBody($aTableBody),
				'selectable'
			);

			$this->Form->appendChild($table);

			$tableActions = new XMLElement('div');
			$tableActions->setAttribute('class', 'actions');

			$options = array(
				array(NULL, false, __('With Selected...')),
				array('delete', false, __('Delete'), 'confirm', null, array('data-message' => __('Are you sure you want to delete the selected download keys?')))
			);

			$tableActions->appendChild(Widget::Apply($options));
			$this->Form->appendChild($tableActions);

		}

		public function __actionIndex() {

			if($_POST['with-selected'] == 'delete' && is_array($_POST['items'])) {

				$checked = (is_array($_POST['items'])) ? array_keys($_POST['items']) : null;

				if(!empty($checked)) {

					foreach($checked as $accesskey) {
						$a = DownloadKeyManager::fetchByKey($accesskey);
						if(is_object($a)) DownloadKeyManager::delete($accesskey);
					}
				}

				redirect(SYMPHONY_URL . '/extension/protecteddownloads/managekeys/');
			}
		}
		
		// Both the Edit and New pages need the same form
		public function __viewNew() {
			$this->__form();
		}

		public function __viewEdit() {
			$this->__form();
		}

		public function __form() {

			require_once(TOOLKIT . '/class.field.php');

			// Handle unknown context
			if(!in_array($this->_context[0], array('new', 'edit'))) Administration::instance()->errorPageNotFound();

			if(isset($this->_context[2])) {
				switch($this->_context[2]) {
					case 'saved':
					$this->pageAlert(
						__('Download Key updated at %s.', array(DateTimeObj::getTimeAgo()))
						. ' <a href="' . SYMPHONY_URL . '/extension/protecteddownloads/managekeys/new/" accesskey="c">'
						. __('Create another?')
						. '</a> <a href="' . SYMPHONY_URL . '/extension/protecteddownloads/managekeys/" accesskey="a">'
						. __('View all Download Keys')
						. '</a>'
						, Alert::SUCCESS);
					break;

					case 'created':
					$this->pageAlert(
						__('Download Key created at %s.', array(DateTimeObj::getTimeAgo()))
						. ' <a href="' . SYMPHONY_URL . '/extension/protecteddownloads/managekeys/new/" accesskey="c">'
						. __('Create another?')
						. '</a> <a href="' . SYMPHONY_URL . '/extension/protecteddownloads/managekeys/" accesskey="a">'
						. __('View all Download Keys')
						. '</a>'
						, Alert::SUCCESS);
					break;
				}
			}

			$this->setPageType('form');

			$isOwner = false;

			if(isset($_POST['fields'])) {
				$downloadkey = $this->_DownloadKey;
			} else if($this->_context[0] == 'edit') {
				if(!$accesskey = $this->_context[1]) redirect(SYMPHONY_URL . '/extension/protecteddownloads/managekeys/');

				if(!$downloadkey = DownloadKeyManager::fetchByKey($accesskey)) {
					Administration::instance()->customError(
						__('Download Key not found'),
						__('The Download Key you requested does not exist.')
					);
				}
			} else $downloadkey = new DownloadKey;

			$this->setTitle(__(($this->_context[0] == 'new' ? '%2$s &ndash; %3$s' : '%1$s &ndash; %2$s &ndash; %3$s'), array($downloadkey->get('name'), __('Protected Downloads'), __('Symphony'))));
			$this->appendSubheading(($this->_context[0] == 'new' ? __('New Key') : $downloadkey->get('name')));
			$this->insertBreadcrumbs(array(
				Widget::Anchor(__('View Keys'), SYMPHONY_URL . '/extension/protecteddownloads/managekeys/'),
			));

			// Recipient
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', __('Key Assignment')));

			$div = new XMLElement('div');
			$div->setAttribute('class', 'two columns');

			$label = Widget::Label(__('Name'), NULL, 'column');
			$label->appendChild(Widget::Input('fields[name]', $downloadkey->get('name')));
			$div->appendChild((isset($this->_errors['name']) ? Widget::Error($label, $this->_errors['name']) : $label));

			$label = Widget::Label(__('Organization'), NULL, 'column');
			$label->appendChild(Widget::Input('fields[organization]', $downloadkey->get('organization')));
			$div->appendChild((isset($this->_errors['organization']) ? Widget::Error($label, $this->_errors['organization']) : $label));

			$group->appendChild($div);

			$label = Widget::Label(__('Email Address'));
			$label->appendChild(Widget::Input('fields[email]', $downloadkey->get('email')));
			$group->appendChild((isset($this->_errors['email']) ? Widget::Error($label, $this->_errors['email']) : $label));

			$label = Widget::Label(__('Notes'));
			$label->appendChild(Widget::Textarea('fields[notes]', 5, 50, $downloadkey->get('notes')));
			$group->appendChild((isset($this->_errors['notes']) ? Widget::Error($label, $this->_errors['notes']) : $label));

			$this->Form->appendChild($group);

			// Key Details
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', __('Key Details')));

			$div = new XMLElement('div');
			$div->setAttribute('class', 'two columns');

			$label = Widget::Label(__('Remaining Lifetime Days from Now'), NULL, 'column');
			$label->appendChild(Widget::Input('fields[remainingdays]', (string)$downloadkey->getRemainingDays()));
			$div->appendChild((isset($this->_errors['remainingdays']) ? Widget::Error($label, $this->_errors['remainingdays']) : $label));

			$label = Widget::Label(__('Remaining Downloads from Now'), NULL, 'column');
			$label->appendChild(Widget::Input('fields[remainingdownloads]', (string)$downloadkey->getRemainingDownloads()));
			$div->appendChild((isset($this->_errors['remainingdownloads']) ? Widget::Error($label, $this->_errors['remainingdownloads']) : $label));

			$group->appendChild($div);

			$label = Widget::Label(__('File'));
			$sections = SectionManager::fetch(NULL, 'ASC', 'sortorder');
			$options = array();
			// If the Author is the Developer, allow them to set the Default Area to
			// be the Sections Index.
/*			if($key->isDeveloper()) {
				$options[] = array('/blueprints/sections/', $key->get('default_area') == '/blueprints/sections/', __('Sections Index'));
			}*/
			if(is_array($sections) && !empty($sections)) {
				foreach($sections as $s) {
					$options[] = array($s->get('accesskey'), $downloadkey->get('file') == $s->get('accesskey'), $s->get('name'));
				}
			}
			$label->appendChild(Widget::Select('fields[file]', $options));
			$group->appendChild($label);

			if (!Administration::instance()->Author->isDeveloper() || $this->_context[0] == 'new') {
				$keyfieldattributes = array('disabled' => 'disabled');
			} else {
				$keyfieldattributes = NULL;
			}
			$label = Widget::Label(__('Access Key'));
			$label->appendChild(Widget::Input('fields[accesskey]', $downloadkey->get('accesskey'), 'text', $keyfieldattributes));
			$group->appendChild((isset($this->_errors['accesskey']) ? Widget::Error($label, $this->_errors['accesskey']) : $label));

			$this->Form->appendChild($group);
			
			// Submit Buttons
			$div = new XMLElement('div');
			$div->setAttribute('class', 'actions');

			$div->appendChild(Widget::Input(
				'action[save]',
				($this->_context[0] == 'edit' ? __('Save Changes') : __('Create New Key')),
				'submit',
				array('accesskey' => 's')
			));

			if($this->_context[0] == 'edit') {
				$button = new XMLElement('button', __('Delete Key'));
				$button->setAttributeArray(array(
					'name' => 'action[delete]',
					'class' => 'button confirm delete',
					'title' => __('Delete this key'),
					'type' => 'submit',
					'accesskey' => 'd',
					'data-message' => __('Are you sure you want to delete this download key?')
				));
				$div->appendChild($button);
			}

			$this->Form->appendChild($div);

		}

		public function __actionNew() {

			if(@array_key_exists('save', $_POST['action']) || @array_key_exists('done', $_POST['action'])) {

				$fields = $_POST['fields'];

				$this->_DownloadKey = new DownloadKey;
				$this->_DownloadKey->set('name', $fields['name']);
				$this->_DownloadKey->set('organization', $fields['organization']);
				$this->_DownloadKey->set('email', $fields['email']);
				$this->_DownloadKey->set('notes', $fields['notes']);
				$this->_DownloadKey->set('issuedstamp', time());
				$this->_DownloadKey->set('expirestamp', time() + ($fields['remainingdays'] * 86400));
				$this->_DownloadKey->set('maxdownloads', $fields['remainingdownloads']);
				$this->_DownloadKey->set('numdownloads', 0);
				$this->_DownloadKey->set('file', $fields['file']);
				
//				if (!isset($fields['accesskey'])) $fields['accesskey'] = $this->_DownloadKey->createAccessKey();
	//			$this->_DownloadKey->set('accesskey', $fields['accesskey']);

//				if($this->_DownloadKey->validate($this->_errors)) {
					$accesskey = $this->_DownloadKey->commit();
					redirect(SYMPHONY_URL . '/extension/protecteddownloads/managekeys/edit/' . $accesskey . '/created/');					
//				}

				if(is_array($this->_errors) && !empty($this->_errors)) {
					$this->pageAlert(__('There were some problems while attempting to save. Please check below for problem fields.'), Alert::ERROR);
				} else {
					$this->pageAlert(
						__('Unknown errors occurred while attempting to save.')
						. '<a href="' . SYMPHONY_URL . '/system/log/">'
						. __('Check your activity log')
						. '</a>.'
						, Alert::ERROR);
				}
			}
		}

		public function __actionEdit() {

//			if(!$author_id = (int)$this->_context[1]) redirect(SYMPHONY_URL . '/system/authors/');

//			$isOwner = ($author_id == Administration::instance()->Author->get('id'));

			if(@array_key_exists('save', $_POST['action']) || @array_key_exists('done', $_POST['action'])) {

				$fields = $_POST['fields'];
				$this->_DownloadKey = DownloadKeyManager::fetchByKey($fields['accesskey']);
				$authenticated = false;

				if($fields['name'] != $this->_DownloadKey->get('name')) $changing_name = true;
				if($fields['organization'] != $this->_DownloadKey->get('organization')) $changing_organization = true;
				if($fields['email'] != $this->_DownloadKey->get('email')) $changing_email = true;
				if($fields['notes'] != $this->_DownloadKey->get('notes')) $changing_email = true;

/*				$this->_Author->set('id', $author_id);*/

/*				if ($this->_Author->isPrimaryAccount() || ($isOwner && Administration::instance()->Author->isDeveloper())) {
					$this->_Author->set('user_type', 'developer'); // Primary accounts are always developer, Developers can't lower their level
				}
				elseif (Administration::instance()->Author->isDeveloper() && isset($fields['user_type'])) {
					$this->_Author->set('user_type', $fields['user_type']); // Only developer can change user type
				}*/

				$this->_DownloadKey->set('name', $fields['name']);
				$this->_DownloadKey->set('organization', $fields['organization']);
				$this->_DownloadKey->set('email', $fields['email']);
				$this->_DownloadKey->set('notes', $fields['notes']);
				$this->_DownloadKey->set('issuedstamp', time());
				$this->_DownloadKey->set('expirestamp', time() + ($fields['remainingdays'] * 86400));
				$this->_DownloadKey->set('maxdownloads', $fields['remainingdownloads']);
				$this->_DownloadKey->set('numdownloads', 0);
				$this->_DownloadKey->set('file', $fields['file']);
				$this->_DownloadKey->set('accesskey', $fields['accesskey']);

				/*$accesskey = */$this->_DownloadKey->commit();

/*				// Don't allow authors to set the Section Index as a default area
				// If they had it previously set, just save `null` which will redirect
				// the Author (when logging in) to their own Author record
				if(
					$this->_Author->get('user_type') == 'author'
					&& $fields['default_area'] == '/blueprints/sections/'
				) {
					$this->_Author->set('default_area', null);
				}
				else {
					$this->_Author->set('default_area', $fields['default_area']);
				}*/

/*				$this->_Author->set('auth_token_active', ($fields['auth_token_active'] ? $fields['auth_token_active'] : 'no'));*/

//				if($this->_Author->validate($this->_errors)) {
//					if(!$authenticated && ($changing_password || $changing_email)){
//						if($changing_password) $this->_errors['old-password'] = __('Wrong password. Enter old password to change it.');
//						elseif($changing_email) $this->_errors['old-password'] = __('Wrong password. Enter old one to change email address.');
//					}

//					elseif(($fields['password'] != '' || $fields['password-confirmation'] != '') && $fields['password'] != $fields['password-confirmation']){
//						$this->_errors['password'] = $this->_errors['password-confirmation'] = __('Passwords did not match');
//					}
//					elseif($this->_Author->commit()){

//						Symphony::Database()->delete('tbl_forgotpass', " `expiry` < '".DateTimeObj::getGMT('c')."' OR `author_id` = '".$author_id."' ");

//						if($isOwner) Administration::instance()->login($this->_Author->get('username'), $this->_Author->get('password'), true);

						/**
						 * After editing an author, provided with the Author object
						 *
						 * @delegate AuthorPostEdit
						 * @since Symphony 2.2
						 * @param string $context
						 * '/system/authors/'
						 * @param Author $author
						 * An Author object
						 */
//						Symphony::ExtensionManager()->notifyMembers('AuthorPostEdit', '/system/authors/', array('author' => $this->_Author));

//						redirect(SYMPHONY_URL . '/system/authors/edit/' . $author_id . '/saved/');
//					}

/*					else {
						$this->pageAlert(
							__('Unknown errors occurred while attempting to save.')
							. '<a href="' . SYMPHONY_URL . '/system/log/">'
							. __('Check your activity log')
							. '</a>.'
							, Alert::ERROR
						);
					}*/
//				}
//				else if(is_array($this->_errors) && !empty($this->_errors)) {
//					$this->pageAlert(__('There were some problems while attempting to save. Please check below for problem fields.'), Alert::ERROR);
//				}
			}
			else if(@array_key_exists('delete', $_POST['action'])) {

				/**
				 * Prior to deleting an author, provided with the Author ID.
				 *
				 * @delegate AuthorPreDelete
				 * @since Symphony 2.2
				 * @param string $context
				 * '/system/authors/'
				 * @param integer $author_id
				 * The ID of Author ID that is about to be deleted
				 */
				Symphony::ExtensionManager()->notifyMembers('AuthorPreDelete', '/system/authors/', array('author_id' => $author_id));

				if(!$isOwner) {
					AuthorManager::delete($author_id);
					redirect(SYMPHONY_URL . '/system/authors/');
				}
				else {
					$this->pageAlert(__('You cannot remove yourself as you are the active Author.'), Alert::ERROR);
				}
			}
		}
	}
?>
