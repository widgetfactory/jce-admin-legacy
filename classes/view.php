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

class WFView extends WFViewBase {

    /**
     * Array of linked scripts
     *
     * @var    array
     */
    protected $scripts = array();

    /**
     * Array of linked style sheets
     *
     * @var    array
     */
    protected $stylesheets = array();

    /**
     * Array of included style declarations
     *
     * @var    array
     */
    protected $styles = array();

    /**
     * Array of scripts placed in the header
     *
     * @var    array
     */
    protected $javascript = array();

    public function display($tpl = null) {
        $document = JFactory::getDocument();
        
        foreach ($this->scripts as $script) {
            $document->addCustomTag('<script type="text/javascript" src="' . $script . '"></script>');
        }
        
        $head = array();
        
        foreach ($this->javascript as $script) {
            $head[] = '<script type="text/javascript">' . $script . '</script>';
        }
        
        foreach ($this->stylesheets as $style) {
            $document->addCustomTag('<link type="text/css" rel="stylesheet" href="' . $style . '" />');
        }
        
        foreach ($this->styles as $style) {
            $head[] = '<style type="text/css">' . $style . '></style>';
        }
        
        if (!empty($head)) {
            $document->addCustomTag(implode("\n", $head));
        }

        parent::display($tpl);
    }

    public function addScript($url) {
        $this->scripts[] = $url;
    }

    public function addStyleSheet($url) {
        $this->stylesheets[] = $url;
    }

    public function addScriptDeclaration($text) {
        $this->javascript[] = $text;
    }

    public function addStyleDeclaration($text) {
        $this->styles[] = $text;
    }

}

?>
