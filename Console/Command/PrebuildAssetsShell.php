<?php
ini_set('memory_limit', '64M');

App::uses('View', 'View');
App::uses('AssetHelper', 'Assets.View/Helper');
App::uses('Folder', 'Utility');

class PrebuildAssetsShell extends Shell {
	var $langs = array(null);
/**
 * Prebuilds all css and js assets
 *
 * @return void
 * @author Tim Koschuetzki
 */
	function main() {
		$this->out('Running prebuild assets shell.');

		if (isset($this->params['lang'])) {
			$this->langs = explode(',', $this->params['lang']);
		}

		$View = new View(null);
		$this->AssetHelper = new AssetHelper($View);
		$this->AssetHelper->settings['cleanDir'] = false;

		$this->out('Prebuilding css .. ', false);
		$start = array_sum(explode(' ', microtime()));
		$this->prebuildCssAggregation();
		$end = array_sum(explode(' ', microtime()));
		$this->out('took ' . round($end - $start, 2) . 's');

		$this->out('Prebuilding js .. ', false);
		$start = array_sum(explode(' ', microtime()));
		$this->prebuildJsAggregation();
		$end = array_sum(explode(' ', microtime()));
		$this->out('took ' . round($end - $start, 2) . 's');
	}


/**
 * check prerequisites for this plugin
 *
 */
	public function init() {
		// check for aggregate folders
		foreach (array('css', 'js') as $type) {
			$path = constant(strtoupper($type)) . 'aggregate';
			if (!file_exists($path)) {
				$this->out('creating '.$path);
				$f = new Folder($path, true, 0755);
			} else {
				$this->out('<success>'.$path.' exists. Skipping...</success>');
			}

			$inc = APP. 'Config' . DS . $type . '_includes.php';
			if (!file_exists($inc)) {
				$this->out('<warning>please setup your includes in '.$inc.'</warning>');

			}
		}

		// check for app/Config/css_includes, js_includes

	}
/**
 * Prepbuilds all js assets
 *
 * @return void
 * @author Tim Koschuetzki
 */
	public function prebuildJsAggregation() {
		$this->_emptyDir(JS . 'aggregate');

		$packages = $this->__getControllerActionPairs('Js');
		$inclusionRules = Configure::read('JsIncludes');
		$layouts = $this->_layouts();

		foreach ($packages as $pair) {
			foreach ($this->langs as $lang) {
				foreach ($layouts as $layout) {
					$pairData = explode(':', $pair);

					$settings = array(
						'type' => 'js',
						'layout' => $layout,
						'params' => array(
							'controller' => Inflector::underscore($pairData[0]),
							'action' => $pairData[1]
						),
						'js' => array(
							'locale' => $lang
						),
						'cleanDir' => false
					);
					$this->AssetHelper->includeFiles($inclusionRules, $settings, false);

					$settings['isIe'] = true;
					$this->AssetHelper->includeFiles($inclusionRules, $settings, false);
				}
			}
		}
	}
/**
 * Prepbuilds all css assets
 *
 * @return void
 * @author Tim Koschuetzki
 */
	public function prebuildCssAggregation() {
		$this->_emptyDir(CSS . 'aggregate');

		$pairs = $this->__getControllerActionPairs('Css');
		$inclusionRules = Configure::read('CssIncludes');

		$result = array();

		$layouts = $this->_layouts();
		foreach ($pairs as $pair) {
			$pairData = explode(':', $pair);
			foreach ($layouts as $layout) {
				$settings = array(
					'type' => 'css',
					'layout' => $layout,
					'params' => array(
						'controller' => Inflector::underscore($pairData[0]),
						'action' => $pairData[1],
					),
					'cleanDir' => false
				);
				$this->AssetHelper->includeFiles($inclusionRules, $settings, false);
			}
		}
	}
/**
 * read available layouts
 *
 * @return string layout files
 * @author Tim Koschuetzki
 */
	private function _layouts() {
		App::import('Core', 'Folder');
		$folder = new Folder(APP . 'View' . DS . 'Layouts');
		$folderContent = $folder->read();
		$result = array();
		foreach ($folderContent[1] as $layout) {
			$result[] = str_replace('.ctp', '', $layout);
		}
		return $result;
	}
/**
 * Gets all unique pairs for controllers and actions from the css includes
 *
 * @return void
 * @author Tim Koschuetzki
 */
	private function __getControllerActionPairs($type = 'css') {
		$type = ucfirst($type);
		$result = array();
		$inclusionRules = Configure::read($type . 'Includes');
		if (empty($inclusionRules)) {
			$this->error('unable to build '.$type.'. No inclusion rules found. Please read the README to see how to setup.');
			return arra();
		}

		foreach ($inclusionRules as $file => $pairs) {
			$pairs = explode(',', $pairs);

			foreach ($pairs as $pair) {
				$pair = trim($pair);
				if (!in_array($pair, $result)) {
					$result[] = $pair;
				}
			}
		}
		return $result;
	}
/**
 * undocumented function
 *
 * @param string $path 
 * @return void
 * @author Tim Koschuetzki
 */
	private function _emptyDir($path) {
		App::uses('Folder', 'Utility');
		$folder = new Folder($path);
		$contents = $folder->read();
		$files = $contents[1];

		foreach ($files as $file) {
			if (strpos($file, '.') === 0) {
				continue;
			}
			@unlink($path . DS . $file);
		}
	}
}
?>
