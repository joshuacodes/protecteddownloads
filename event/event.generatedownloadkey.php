<?php

	require_once(TOOLKIT . '/class.event.php');
	
	Class eventgenerate_download_key extends Event {

		const ROOTELEMENT = 'generate-download-key';

		public $eParamFILTERS = array(
			'admin-only'
		);
			
		public static function about() {
			return array(
				'name' => 'Generate Download Key',
				'author' => array(
					'name' => 'JoshuaCodes',
					'website' => 'http://www.joshuacodes.com',
					'email' => 'joshuacodes@gmail.com'),
				'version' => '0.1',
				'release-date' => '2012-10-03');
		}

		public static function getSource() {
			return false;
		}

		public static function allowEditorToParse() {
			return false;
		}

		public static function documentation() {
			return '
			<h3>Generate Download Key</h3>
			<p>When this event is attached to a page, it allows the generation of keys that can be used to download specific files.  It needs a form that submits </p>

			<h3>Example Front-end Form Markup</h3>
			<p>This is an example of the form markup you can use on your frontend:</p>
			<pre class="XML"><code>&lt;form method="post" action="" enctype="multipart/form-data">
  &lt;input name="MAX_FILE_SIZE" type="hidden" value="2097152" />
  &lt;label>Page Name
    &lt;input name="fields[page-name]" type="text" />
  &lt;/label>
  &lt;label>Main Heading
    &lt;input name="fields[main-heading]" type="text" />
  &lt;/label>
  &lt;label>Content
    &lt;textarea name="fields[content]" rows="15" cols="50">&lt;/textarea>
  &lt;/label>
  &lt;label>Image
    &lt;input name="fields[image]" type="file" />
  &lt;/label>
  &lt;input name="fields[file]" type="hidden" value="..." />
  &lt;input name="action[static]" type="submit" value="Submit" />
&lt;/form>
			</code></pre>
			<p>To edit an existing entry, include the entry ID value of the entry in the form. This is best as a hidden field like so:</p>
			<pre class="XML"><code>&lt;input name="id" type="hidden" value="23" /></code></pre>
			<p>To redirect to a different location upon a successful save, include the redirect location in the form. This is best as a hidden field like so, where the value is the URL to redirect to:</p>
			<pre class="XML"><code>&lt;input name="redirect" type="hidden" value="http://ec2-107-21-171-25.compute-1.amazonaws.com/success/" /></code></pre>
			';
		}
		
		public function load() {
			if(isset($_POST['action']['message'])) return $this->__trigger();
		}

		protected function __trigger() {

		}


/*			if(isset($_POST['lifetime']) && isset($_POST['max-downloads']))
			{
/*				if($keys > 20) { $keys = 20; }

				//	echo $keys;
				// A script to generate unique download keys for the purpose of protecting downloadable goods

				if(empty($_SERVER['REQUEST_URI'])) {
			    	$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
				}

				// Strip off query string so dirname() doesn't get confused
				$url = preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);
				$folderpath = 'http://'.$_SERVER['HTTP_HOST'].'/'.ltrim(dirname($url), '/').'/';
*/
				// Strip slashes if necessary
/*				if (get_magic_quotes_gpc()) {
					$filename = trim(stripslashes($_POST['filename']));
					$max_downloads = trim(stripslashes($_POST['max-downloads']));
					$lifetime = trim(stripslashes($_POST['lifetime']));
//					$note = trim(stripslashes($_POST['note']));
				} else {
					$filename = trim($_POST['filename']);
					$max_downloads = trim($_POST['max-downloads']);
					$lifetime = trim($_POST['lifetime']);
//					$note = trim($_POST['note']);
				}
	
				// Get the activation time
				$time = date('U');
			/*	echo "time: " . $time . "<br />";
	
				echo '<div class="box">';
	
				for ($counter = 1; $counter <= $keys; $counter += 1) {*/
/*				// Generate the unique download key
				$key = substr(uniqid(md5(rand())), 0, 12);
				//	echo "key: " . $key . "<br />";
		
/*		
		// Generate the link
		echo $folderpath . "download.php?id=" . $key . "<br />\n";*/
		
				// Sanitize the query
/*                                $query = "INSERT INTO `tbl_protected_downloads` (download_key,filename,name,email,organization,notes,issued_timestamp,lifetime,max_downloads,num_downloads) VALUES(\"$key\",\"$filename\",'name','email','organization','notes',\"$time\",\"$lifetime\",\"$max_downloads\",'num_downloads')";
		
				// Write the key and other information to the DB as a new row
	Symphony::Database()->query($query . ';');
				//$successkeys++;
/*	}
	
	echo '</div>';
}*/

/*			}
			
			// In case of the page:
			if(isset($_GET['download']))
			{
				header('Content-Disposition: attachment; filename='.$_GET['download']);
			}
			
			// In case of a file:
			if(isset($_GET['file'])) {
				// include_once('event.force_download.config.php');

				$driver = ExtensionManager::getInstance('force_download');
				/* @var $driver extension_force_download */
/*				$allowedDirs = $driver->getLocations();

				$pathInfo = pathinfo($_GET['file']);

				// Check to see if the directory is allowed to direct-download from:
				$wildCardMatch = false;
				$info = pathinfo($_GET['file']);
				foreach($allowedDirs as $allowedDir)
				{
					if(strstr($allowedDir, '/*') !== false)
					{
						$match = str_replace('/*', '', $allowedDir);
						if(strstr($match, $info['dirname']) !== false)
						{
							$wildCardMatch = true;
						}
					}
				}

				if(in_array($pathInfo['dirname'], $allowedDirs) || $wildCardMatch)
				{
					// Force the download:
					if (file_exists($_GET['file'])) {
						// Determine the mimetype:
						if(function_exists('mime_content_type'))
						{
							$mimeType = mime_content_type($_GET['file']);
						} elseif(function_exists('finfo_open')) {
							$finfo = finfo_open(FILEINFO_MIME_TYPE);
							$mimeType = finfo_file($finfo, $_GET['file']);
						} else {
							$mimeType = "application/force-download";
						}
						header('Content-Description: File Transfer');
						header('Content-Type: '.$mimeType);
						header('Content-Disposition: attachment; filename='.$pathInfo['basename']);
						header('Content-Transfer-Encoding: binary');
						header('Expires: 0');
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						header('Pragma: public');
						header('Content-Length: ' . filesize($_GET['file']));
						ob_clean();
						flush();
						readfile($_GET['file']);
						exit;
					} else {
						die('File does not exist!');
					}
				} else {
					die('Permission denied!');
				}
			}
		}*/
/*			include(TOOLKIT . '/events/event.section.php');
			return $result;*/
	}
?>
