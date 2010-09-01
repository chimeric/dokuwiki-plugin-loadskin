<?php
/**
 * DokuWiki Plugin Loadskin
 *
 * Michael Klier <chi@chimeric.de>
 */
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'admin.php');
 
/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_loadskin extends DokuWiki_Admin_Plugin {
 		var $cmd;

    /**
     * Constructor
     */
    function admin_plugin_loadskin() {
        $this->setupLocale();
        $this->config = DOKU_INC.'conf/loadskin.conf';
    }

    /**
     * return some info
     */
    function getInfo() {
        return array(
            'author' => 'Michael Klier',
            'email'  => 'chi@chimeric.de',
            'date'   => '2008-06-22',
            'name'   => 'TemplatTemplatee Manager',
            'desc'   => 'Allows to change the used template for a namespace or certain pages',
            'url'    => 'http://wiki.splitbrain.org/plugin:loadskin',
        );
    }
 
    /**
     * return sort order for position in admin menu
     */
    function getMenuSort() {
        return 300;
    }
 
    /**
     * handle user request
     */
    function handle() {
        $data = array();

        if(!empty($_REQUEST['pattern'])) {
            $id = cleanID($_REQUEST['pattern']);
            
            if($_REQUEST['act'] == 'add') {
                if(@file_exists($this->config)) {
                    $data = unserialize(io_readFile($this->config, false));
                    $data[$id] = $_REQUEST['tpl'];
                    io_saveFile($this->config, serialize($data));
                } else {
                    $data[$id] = $_REQUEST['tpl'];
                    io_saveFile($this->config, serialize($data));
                }
            }

            if($_REQUEST['act'] == 'del') {
                $data = unserialize(io_readFile($this->config, false));
                unset($data[$id]);
                io_saveFile($this->config, serialize($data));
            }
        }
    }
 
    /**
     * output appropriate html
     */
    function html() {
        global $lang;
        print $this->plugin_locale_xhtml('intro');

        $form = new Doku_Form(array());
        $form->startFieldSet('Add rule');
        $form->addHidden('id',$ID);
        $form->addHidden('do','admin');
        $form->addHidden('page','loadskin');
        $form->addHidden('act','add');
        $form->addElement(form_makeOpenTag('p'));
        $form->addElement(form_makeTextField('pattern','',$lang['mu_namespace']));
        $form->addElement(form_makeCloseTag('p'));
        $form->addElement(form_makeOpenTag('p'));
        $form->addElement(form_makeListboxField('tpl',$this->getTemplates(),'',$this->getLang('template')));
        $form->addElement(form_makeCloseTag('p'));
        $form->addElement(form_makeButton('submit','admin',$lang['btn_save']));
        $form->endFieldSet();
        $form->printForm();

        echo '<br />' . DOKU_LF;
        echo '<br />' . DOKU_LF;

        if(@file_exists($this->config)) {
            $data = unserialize(io_readFile($this->config, false));

			if(!empty($data)) {
				echo '<table class="inline">' . DOKU_LF;
				echo '  <tr>' . DOKU_LF;
				echo '    <th>' . $lang['mu_namespace'] . '</th>' . DOKU_LF;
				echo '    <th>' . $this->getLang('template') . '</th>' . DOKU_LF;
				echo '    <th>&nbsp;</th>' . DOKU_LF;
				echo '  </tr>' . DOKU_LF;
				foreach($data as $key => $value) {
					echo '  <tr>' . DOKU_LF;
					echo '    <td>' . $key . '</td>' . DOKU_LF;
					echo '    <td>' . $value . '</td>' . DOKU_LF;
					echo '    <td>' . DOKU_LF;

                    $form = new Doku_Form(array());
                    $form->addHidden('do','admin');
                    $form->addHidden('page','loadskin');
                    $form->addHidden('act','del');
                    $form->addHidden('id',$ID);
                    $form->addHidden('pattern',$key);
                    $form->addElement(form_makeButton('submit','admin',$lang['btn_delete']));
                    $form->printForm();

					echo '    </td>' . DOKU_LF;
					echo '  </tr>' . DOKU_LF;
				}
				echo '</table>' . DOKU_LF;
			}
        }
    }

	/**
	 * Returns an array of availabel templates to choose from
	 *
	 * Michael Klier <chi@chimeric.de>
	 */
    function getTemplates() {
        $tpl_dir = DOKU_INC.'lib/tpl/';
        if ($dh = @opendir($tpl_dir)) {
            while (false !== ($entry = readdir($dh))) {
                if ($entry == '.' || $entry == '..') continue;
                if (!preg_match('/^[\w-]+$/', $entry)) continue;

                $file = (is_link($this->_dir.$entry)) ? readlink($tpl_dir.$entry) : $entry;
                if (is_dir($tpl_dir.$file)) $list[] = $entry;
            }
            closedir($dh);
            sort($list);
        }
        return $list;
    }
}
//vim:ts=4:sw=4:et:enc=utf-8:
