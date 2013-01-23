<?php

/**
 * @package   	JCE
 * @copyright 	Copyright (c) 2009-2013 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('JPATH_BASE') or die('RESTRICTED');

class WFTableProfiles extends JTable {

    /**
     * Primary Key
     *
     *  @var int
     */
    var $id = null;

    /**
     *
     *
     * @var varchar
     */
    var $name = null;

    /**
     *
     *
     * @var varchar
     */
    var $description = null;

    /**
     *
     *
     * @var varchar
     */
    var $components = null;

    /**
     *
     *
     * @var int
     */
    var $area = null;

    /**
     *
     *
     * @var varchar
     */
    var $device = null;

    /**
     *
     *
     * @var varchar
     */
    var $users = null;

    /**
     *
     *
     * @var varchar
     */
    var $types = null;

    /**
     *
     *
     * @var varchar
     */
    var $rows = null;

    /**
     *
     *
     * @var varchar
     */
    var $plugins = null;

    /**
     *
     *
     * @var tinyint
     */
    var $published = 0;

    /**
     *
     *
     * @var tinyint
     */
    var $ordering = 1;

    /**
     *
     *
     * @var int unsigned
     */
    var $checked_out = 0;

    /**
     *
     *
     * @var datetime
     */
    var $checked_out_time = "";

    /**
     *
     *
     * @var text
     */
    var $params = null;

    public function __construct(& $db) {
        parent::__construct('#__wf_profiles', 'id', $db);
    }

    private static function cleanInput($input, $method = 'string') {
        $filter = JFilterInput::getInstance();
        $input = (array) $input;

        for ($i = 0; $i < count($input); $i++) {
            $input[$i] = $filter->clean($input[$i], $method);
        }

        return $input;
    }

    public function bind($data, $ignore = '') {
        $filter = JFilterInput::getInstance();

        $data = (array) $data;

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'name':
                case 'description':
                    $value = $filter->clean($value);
                    break;
                case 'components':
                case 'device':
                    $value = implode(',', self::cleanInput($value));
                    break;
                case 'usergroups':
                    $key    = 'types';
                    $value  = implode(',', self::cleanInput($value, 'int'));
                    break;
                case 'users':
                    $value = implode(',', self::cleanInput($value, 'int'));
                    break;
                case 'area':
                    if (empty($value) || count($value) == 2) {
                        $value = 0;
                    } else {
                        $value = $value[0];
                    }
                    break;
                case 'params':
                    $json = array();

                    // decode json string
                    if (is_string($value)) {
                        $value = json_decode($value, true);
                    }

                    $value = (array) $value;

                    if (array_key_exists('editor', $value)) {
                        $json['editor'] = $value['editor'];
                    }
                    // get plugins
                    $plugins = explode(',', $data['plugins']);

                    foreach ($plugins as $plugin) {
                        // add plugin params to array
                        if (array_key_exists($plugin, $value)) {
                            $json[$plugin] = $value[$plugin];
                        }
                    }

                    $value = json_encode($json);

                    break;
            }
            $data[$key] = $value;
        }

        return parent::bind($data, $ignore);
    }

    /**
     * Overloaded check function

     */
    public function check() {
        if (trim($this->name) == '') {
            $this->setError(WText::_('WF_PROFILES_VALID_NAME'));
            return false;
        }

        if (empty($this->types)) {
            $this->setError(WText::_('WF_PROFILES_SELECT_USERGROUPS'));
            return false;
        }

        return true;
    }

}

?>