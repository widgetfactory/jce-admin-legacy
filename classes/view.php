<?php

jimport('joomla.application.component.view');

if (!class_exists('WFViewBase')) {
    if (interface_exists('JView')) {

        abstract class WFViewBase extends JViewLegacy {
            
        }

    } else {

        abstract class WFViewBase extends JView {
            
        }

    }
}
?>
