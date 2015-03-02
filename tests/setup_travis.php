<?php
/**
 *
 *
 *
 *
 */


$magentoTargetVersion = getenv('MAGENTO');
$moduleRoot = realpath(__DIR__.'/../');

$passthru = function($shellCommand) {
    echo $shellCommand.PHP_EOL;
    passthru($shellCommand);
};

if (strpos($magentoTargetVersion, '1.')===0) {
    $passthru('vendor/bin/mage-ci install test-root $MAGENTO magento_test -c -t -r http://mage-ci.ecomdev.org');
    $passthru('vendor/bin/mage-ci install-module test-root '.$moduleRoot);
} elseif (strpos($magentoTargetVersion, '2.')===0) {
    echo "I think we need a way to install mage2 in a similar way".PHP_EOL;
} else {
    fwrite(STDERR, "Version Identifier '$magentoTargetVersion' not recogniezed".PHP_EOL);
    die(1);
};
