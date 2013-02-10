<?php

	/**
	 * This page is intended to provide sample code for utilizing all functions
	 * of the Protected Downloads extension.
	 */

	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(CONTENT . '/class.sortable.php');
	require_once(CONTENT . '/../../extensions/protecteddownloads/lib/class.downloadkeymanager.php');

	define_safe('BASE_URL', URL . '/symphony/extension/protecteddownloads/managekeys');

	Class contentExtensionProtectedDownloadsSampleCode extends AdministrationPage {

		public function sort(&$sort, &$order, $params){
			if(is_null($sort)) $sort = 'issuedtimestamp';

			return DownloadKeyManager::fetch($sort, $order);
		}

		public function __viewIndex() {
			$this->setPageType('table');
			$this->setTitle(__('%1$s &ndash; %2$s', array(__('Protected Downloads'), __('Symphony'))));

			if (Administration::instance()->Author->isDeveloper()) {
				$this->appendSubheading(__('Sample Code - NOT YET IMPLEMENTED'), Widget::Anchor(
					__('New Key'),
					Administration::instance()->getCurrentPageURL().'new/',
					__('Create a new key'),
					'create button',
					NULL,
					array('accesskey' => 'c')
				));
			} else $this->appendSubheading(__('Sample Code - NOT YET IMPLEMENTED'));

/*			Sortable::initialize($this, $keys, $sort, $order);

			$columns = array(
				array(
					'label' => __('Date Issued'),
					'sortable' => true,
					'handle' => 'issuedtimestamp'
				),
				array(
					'label' => __('Name'),
					'sortable' => true,
					'handle' => 'name'
				),
				array(
					'label' => __('Download Key'),
					'sortable' => true,
					'handle' => 'downloadkey'
				),
				array(
					'label' => __('File'),
					'sortable' => true,
					'handle' => 'file'
				),
				array(
					'label' => __('Expiration'),
					'sortable' => true,
					'handle' => 'expiration'
				),
				array(
					'label' => __('Remaining Downloads'),
					'sortable' => true,
					'handle' => 'remaining'
				),
			);

			$aTableHead = Sortable::buildTableHeaders(
				$columns,
				$sort,
				$order,
				(isset($_REQUEST['filter']) ? '&amp;filter=' . $_REQUEST['filter'] : '')
			);

			$aTableBody = array();

			if(!is_array($keys) || empty($keys)) {
				$aTableBody = array(
					Widget::TableRow(array(
						Widget::TableData(
							__('None found.'),
							'inactive',
							NULL,
							count($aTableHead)
						)
					),	'odd')
				);
			} else {
				foreach($keys as $a) {
					// Setup each cell
					$td1 = Widget::TableData(
						DateTimeObj::format($a->get('issuedtimestamp'), __SYM_DATETIME_FORMAT__)
					);

					$td2 = Widget::TableData(Widget::Anchor($a->get('email'), 'mailto:'.$a->get('email'), __('Email this person.')));

					if(Administration::instance()->Author->isDeveloper()) {
						$td3 = Widget::TableData(
							Widget::Anchor(
								$a->get('downloadkey'),
								Administration::instance()->getCurrentPageURL() . 'edit/' . $a->get('downloadkey', __('View details and edit this key.'))
							)
						);
					} else {
						$td3 = Widget::TableData($a->get('downloadkey'));
					}

					$td4 = Widget::TableData($a->get('file'));

					$td5 = Widget::TableData($a->get('lifetime'));

					$td6 = Widget::TableData($a->get('numdownloads'));*/

					// Need to decide how to use this.
/*					if (Administration::instance()->Author->isDeveloper()) {*/
/*						if ($a->get('id') != Administration::instance()->Author->get('id')) {*/
/*							$td3->appendChild(Widget::Input('items['.$a->get('downloadkey').']', NULL, 'checkbox'));*/
/*						}*/
/*					}*/

					// Add a row to the body array, assigning each cell to the row
					/*if(Administration::instance()->Author->isDeveloper())
						$aTableBody[] = Widget::TableRow(array($td1, $td2, $td3, $td4, $td5));
					else*/
/*						$aTableBody[] = Widget::TableRow(array($td1, $td2, $td3, $td4, $td5, $td6));
				}
			}*/

/*			$table = Widget::Table(
				Widget::TableHead($aTableHead),
				NULL,
				Widget::TableBody($aTableBody),
				'selectable'
			);

			$this->Form->appendChild($table);

			if(Administration::instance()->Author->isDeveloper()) {
				$tableActions = new XMLElement('div');
				$tableActions->setAttribute('class', 'actions');

				$options = array(
					array(NULL, false, __('With Selected...')),
					array('delete', false, __('Delete'), 'confirm', null, array(
						'data-message' => __('Are you sure you want to delete the selected download keys?')
					))
				);*/

				/**
				* Allows an extension to modify the existing options for this page's
				* With Selected menu. If the `$options` parameter is an empty array,
				* the 'With Selected' menu will not be rendered.
				*
				* @delegate AddCustomActions
				* @since Symphony 2.3.2
				* @param string $context
				* '/system/authors/'
				* @param array $options
				* An array of arrays, where each child array represents an option
				* in the With Selected menu. Options should follow the same format
				* expected by `Widget::__SelectBuildOption`. Passed by reference.
				*/
/*				Symphony::ExtensionManager()->notifyMembers('AddCustomActions', '/system/authors/', array(
					'options' => &$options
				));

				if(!empty($options)) {
					$tableActions->appendChild(Widget::Apply($options));
					$this->Form->appendChild($tableActions);
				}
			}*/
		}

		public function __actionIndex() {
			
		}

	}
?>
