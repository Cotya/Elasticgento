<?php
/**
 *
 *
 *
 *
 */


$magentoTargetVersion = getenv('MAGENTO');

if (strpos($magentoTargetVersion, '1.')===0) {
    passthru('vendor/bin/mage-ci install test-root $MAGENTO magento_test -c -t -r http://mage-ci.ecomdev.org');
    passthru('vendor/bin/mage-ci install-module test-root $CURR_DIR');
} elseif (strpos($magentoTargetVersion, '2.')===0) {
    echo "I think we need a way to install mage2 in a similar way";
} else {
    fwrite(STDERR, "Version Identifier '$magentoTargetVersion' not recogniezed");
    die(1);
};
