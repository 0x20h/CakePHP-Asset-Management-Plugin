<?php
try {
	Configure::load('css_includes.php');
} catch (ConfigureException $e) {
	trigger_error($e->getMessage(), E_USER_NOTICE);
}

try {
	Configure::load('js_includes.php');
} catch (ConfigureException $e) {
	trigger_error($e->getMessage(), E_USER_NOTICE);
}
?>
