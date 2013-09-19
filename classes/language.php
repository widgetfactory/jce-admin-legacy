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
defined('_JEXEC') or die('RESTRICTED');

/* https://code.google.com/p/web-tom/source/browse/trunk/includes/parse_ini_string.php */
if (!function_exists('parse_ini_string')) {

    function parse_ini_string($str, $process_sections = false) {
        $lines = explode("\n", $str);
        $return = Array();
        $inSect = false;
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line || $line[0] == "#" || $line[0] == ";")
                continue;
            if ($line[0] == "[" && $endIdx = strpos($line, "]")) {
                $inSect = substr($line, 1, $endIdx - 1);
                continue;
            }
            if (!strpos($line, '=')) // (We don't use "=== false" because value 0 is not valid as well)
                continue;

            $tmp = explode("=", $line, 2);
            $tmp[1] = substr($tmp[1], 1, strlen($tmp[1]) - 2);
            if ($process_sections && $inSect)
                $return[$inSect][trim($tmp[0])] = ltrim($tmp[1]);
            else
                $return[trim($tmp[0])] = ltrim($tmp[1]);
        }
        return $return;
    }

}

class WFLanguageParser extends JObject {

    protected $mode = 'editor';
    protected $plugins = array();
    protected $sections = array();

    function __construct($config = array()) {

        if (array_key_exists('plugins', $config)) {
            $config['plugins'] = (array) $config['plugins'];
        }

        if (array_key_exists('sections', $config)) {
            $config['sections'] = (array) $config['sections'];
        }

        $this->setProperties($config);
    }

    protected static function processLanguageINI($files, $sections = array(), $filter = '') {
        $data = array();

        foreach ((array) $files as $file) {
            $ini = false;

            $content = file_get_contents($file);

            if ($content) {
                $ini = @parse_ini_string($content, true);
            }

            if ($ini && is_array($ini)) {
                // only include these keys
                if (!empty($sections)) {
                    $ini = array_intersect_key($ini, array_flip($sections));
                }

                // filter keys by regular expression
                if ($filter) {
                    foreach (array_keys($ini) as $key) {
                        if (preg_match('#' . $filter . '#', $key)) {
                            unset($ini[$key]);
                        }
                    }
                }

                $data = array_merge($data, $ini);
            }
        }

        $output = '';

        if (!empty($data)) {

            $x = 0;

            foreach ($data as $key => $strings) {

                if (is_array($strings)) {
                    $output .= '"' . strtolower($key) . '":{';

                    $i = 0;

                    foreach ($strings as $k => $v) {
                        if (is_numeric($v)) {
                            $v = (float) $v;
                        } else {
                            $v = '"' . $v . '"';
                        }

                        // key to lowercase
                        $k = strtolower($k);

                        // get position of the section name in the key if any
                        $pos = strpos($k, $key . '_');

                        // remove the section name
                        if ($pos === 0) {
                            $k = substr($k, strlen($key) + 1);
                        }

                        // hex colours to uppercase and remove marker
                        if (strpos($k, 'hex_') !== false) {
                            $k = strtoupper(str_replace('hex_', '', $k));
                        }

                        // create key/value pair as JSON string
                        $output .= '"' . $k . '":' . $v . ',';

                        $i++;
                    }
                    // remove last comma
                    $output = rtrim(trim($output), ',');

                    $output .= "},";

                    $x++;
                }
            }
            // remove last comma
            $output = rtrim(trim($output), ',');
        }
        return $output;
    }

    private function getFilter() {
        switch ($this->get('mode')) {
            case 'editor':
                return '(dlg|_dlg)$';
                break;
            case 'plugin':
                return '';
                break;
        }
    }

    public function load($files = array()) {
        // get the language file
        $language = JFactory::getLanguage();
        // get language tag
        $tag = $language->getTag();
        // base language path
        $path = JPATH_SITE . '/language/' . $tag;

        // if no file set
        if (empty($files)) {
            // Add English language
            $files[] = JPATH_SITE . '/language/en-GB/en-GB.com_jce.ini';

            // non-english language
            if ($tag != 'en-GB') {
                if (is_dir($path)) {
                    $file = $path . '/' . $tag . '.com_jce.ini';

                    if (is_file($file)) {
                        $files[] = $file;
                    } else {
                        $tag = 'en-GB';
                    }
                } else {
                    $tag = 'en-GB';
                }
            }

            $plugins = $this->get('plugins');

            if (!empty($plugins)) {
                foreach ($plugins as $plugin) {
                    // add English file
                    $ini = JPATH_SITE . '/language/en-GB/en-GB.com_jce_' . $plugin . '.ini';

                    if (is_file($ini)) {
                        $files[] = $ini;
                    }

                    // non-english language
                    if ($tag != 'en-GB') {
                        $ini = JPATH_SITE . '/language/' . $tag . '/' . $tag . '.com_jce_' . $plugin . '.ini';

                        if (is_file($ini)) {
                            $files[] = $ini;
                        }
                    }
                }
            }
        }

        $sections = $this->get('sections');
        $filter = $this->getFilter();

        $data = self::processLanguageINI($files, $sections, $filter);
        // shorten the tag, eg: en-GB -> en
        $tag = substr($tag, 0, strpos($tag, '-'));

        // clean data
        $data = rtrim(trim($data), ',');

        return 'tinyMCE.addI18n({"' . $tag . '":{' . $data . '}});';
    }

    public function output($data) {
        if ($data) {
            ob_start();

            header("Content-type: application/javascript; charset: UTF-8");
            header("Vary: Accept-Encoding");

            // expires after 2 days
            $expires = 60 * 60 * 24 * 2;

            header("Cache-Control: maxage=" . $expires);

            // Handle proxies
            header("Expires: " . gmdate("D, d M Y H:i:s", time() + $expires) . " GMT");

            // get content hash
            $hash = hash('md5', $data);

            // set etag header
            header("ETag: \"{$hash}\"");

            // set content length
            header("Content-Length: " . strlen($data));

            echo $data;

            exit(ob_get_clean());
        }
        exit();
    }

}

?>
