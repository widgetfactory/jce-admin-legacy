<?php

jimport('joomla.application.component.view');

if (!class_exists('WFView')) {
    if (interface_exists('JView')) {

        abstract class WFView extends JViewLegacy {
            
        }

    } else {

        class WFView extends JView {
            
        }

    }
}
?>
