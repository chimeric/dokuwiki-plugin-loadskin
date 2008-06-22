<?php
/**
 * DokuWiki Action Plugin LoadSkin
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michael Klier <chi@chimeric.de>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');
if(!defined('DOKU_LF')) define('DOKU_LF', "\n");

require_once(DOKU_PLUGIN.'action.php');

/**
 * All DokuWiki plugins to interfere with the event system
 * need to inherit from this class
 */
class action_plugin_loadskin extends DokuWiki_Action_Plugin {

    function getInfo() {
        return array(
                'author' => 'Michael Klier',
                'email'  => 'chi@chimeric.de',
                'date'   => '2008-06-22',
                'name'   => 'loadskin',
            	'desc'   => 'Allows to change the used template for a namespace or certain pages',
                'url'    => 'http://wiki.splitbrain.org/plugin:loadskin'
            );
    }

    // register hook
    function register(&$controller) {
        $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'handleMeta');
		$controller->register_hook('DOKUWIKI_STARTED', 'BEFORE', $this, 'handleConf');
    }

	/**
	 * Overwrites the $conf['template'] setting
	 *
	 * Michael Klier <chi@chimeric.de>
	 */
	function handleConf(&$event, $param) {
		global $ID;
		global $conf;

		$config = DOKU_INC.'conf/loadskin.conf';

		if(@file_exists($config)) {
			$data = unserialize(io_readFile($config, false));
			$tpl = $this->getTpl($data, $ID);
			if($tpl && $_REQUEST['do'] != 'admin') {
				$conf['template'] = $tpl;
			}
		}
	}

    /**
	 * Replaces the style headers with a different skin if specified in the
	 * configuration
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    function handleMeta(&$event, $param) {
		global $ID;

		$config = DOKU_INC.'conf/loadskin.conf';

		if(@file_exists($config)) {
			$data = unserialize(io_readFile($config, false));
			$tpl = $this->getTpl($data, $ID);

			if($tpl && $_REQUEST['do'] != 'admin') {
				$head =& $event->data;
				for($i=0; $i<=count($head['link']); $i++) {
					if($head['link'][$i]['rel'] == 'stylesheet') {
						$head['link'][$i]['href'] = preg_replace('/t=([\w]+$)/', "t=$tpl", $head['link'][$i]['href']);
					}
				}
			}
		}
    }

	/**
	 * Checks if a given page should use a different template then the default
	 *
	 * Michael Klier <chi@chimeric.de>
	 */
	function getTpl($data, $id) {
		if($data[$id]) return $data[$id];

    	$path  = explode(':', $id);
    	$found = false;

    	while(count($path) > 0) {
        	$id = implode(':', $path);
			if($data[$id]) return $data[$id];
        	array_pop($path);
    	}
		return false;
	}
}

// vim:ts=4:sw=4:enc=utf-8:
