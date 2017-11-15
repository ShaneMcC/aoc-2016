#!/usr/bin/php
<?php
	$__CLI['long'] = ['buggy', 'sleep:', 'width:', 'height:'];
	require_once(dirname(__FILE__) . '/../common/common.php');

	$input = getInputLines();

	require_once(dirname(__FILE__) . '/screen.php');

	$screenWidth = isset($__CLIOPTS['width']) ? $__CLIOPTS['width'] : (isTest() ? 7 : 50);
	$screenHeight = isset($__CLIOPTS['height']) ? $__CLIOPTS['height'] : (isTest() ? 3 : 6);

	$screen = new Screen($screenWidth, $screenHeight);

	if (isDebug()) { $screen->drawScreen(false); }
	$screen->setDebug(isDebug());
	$screen->setSleep(isset($__CLIOPTS['sleep']) ? $__CLIOPTS['sleep'] : 25000);
	if (isset($__CLIOPTS['buggy'])) { $screen->setBuggy(true); }
	$screen->parseInput($input);
	if (isDebug()) { echo "\n"; }

	$part1 = 0;
	foreach ($screen->getScreen() as $row) { $part1 += substr_count(implode('', $row), $screen->getDisplayChars()[true]); }

	echo 'Part 1: ', $part1, "\n";

	$part2 = '';
	if (!isTest()) {
		$characters = $screen->getScreenCharacters();
		foreach ($characters as $c) { $part2 .= $screen->decodeCharacter($c); }
		echo 'Part 2: ', $part2, "\n";
	}

	if (!isDebug() && (isTest() || empty($part2) || strpos($part2, '?') !== FALSE)) {
		$screen->drawScreen(false);
	}
