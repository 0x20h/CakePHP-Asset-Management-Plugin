<?php
if (!isset($mode)) {
	trigger_error('mode not set, call element with mode (css|js) param');
	return;
}

$rules = Configure::read(Inflector::camelize($mode).'Includes');
$options = array(
	'type' => $mode,
	'packaging' => Configure::read('Assets.packaging'),
	'css' => array(
	),
);
$this->Asset->includeFiles($rules, $options);
?>
