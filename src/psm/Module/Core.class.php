<?php
/**
 * PHP Server Monitor
 * Monitor your servers and websites.
 *
 * This file is part of PHP Server Monitor.
 * PHP Server Monitor is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PHP Server Monitor is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PHP Server Monitor.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     phpservermon
 * @author      Pepijn Over <pep@neanderthal-technology.com>
 * @copyright   Copyright (c) 2008-2014 Pepijn Over <pep@neanderthal-technology.com>
 * @license     http://www.gnu.org/licenses/gpl.txt GNU GPL v3
 * @version     Release: @package_version@
 * @link        http://phpservermon.neanderthal-technology.com/
 **/

namespace psm\Module;

abstract class Core {
	/**
	 * Custom message
	 * @var string $message
	 */
	public $message;

	/**
	 * Current mode. Can be used by modules to determine
	 * what to do
	 * @var string $mode
	 */
	public $mode;

	/**
	 * Add footer to page?
	 * @var boolean $add_footer
	 */
	protected $add_footer = true;

	/**
	 * smDatabase object
	 * @var object $db
	 */
	protected $db;

	/**
	 * \psm\Template object
	 * @var object $tpl
	 */
	protected $tpl;

	/**
	 * Template Id that should be added to the main template
	 * @var string
	 * @see setTemplateId() getTemplateId()
	 */
	protected $tpl_id;

	function __construct() {
		global $db;

		$this->db = ($db) ? $db : new \psm\Database();
		$this->tpl = new \psm\Template();


	}

	/**
	 * Create the HTML code for the module.
	 * First the createHTMLLabels() will be called to add all labels to the template,
	 * Then the tpl_id set in $this->getTemplateId() will be added to the main template automatically
	 */
	public function createHTML() {
		// add footer to page?
		if($this->add_footer) {
			$this->tpl->newTemplate('main_footer', 'main.tpl.html');
			$html_footer = $this->tpl->getTemplate('main_footer');
		} else {
			$html_footer = '';
		}

		if(psm_get_conf('show_update')) {
			// user wants updates, lets see what we can do
			$this->createHTMLUpdateAvailable();
		}

		$this->createHTMLLabels();

		// add the module's custom template to the main template to get some content
		$this->tpl->addTemplatedata(
			'main',
			array(
				'content' => $this->tpl->getTemplate($this->getTemplateId()),
				'message' => ($this->message == '') ? '&nbsp' : $this->message,
				'html_footer' => $html_footer,
				'label_back_to_top' => psm_get_lang('system', 'back_to_top'),
			)
		);

		// display main template
		echo $this->tpl->display('main');
	}

	/**
	 * First check if an update is available, if there is add a message
	 * to the main template
	 */
	protected function createHTMLUpdateAvailable() {
		// check for updates?

		if(psm_check_updates()) {
			// yay, new update available =D
			$this->tpl->addTemplateData(
				'main',
				array(
					'update_available' => '<div id="update">'.psm_get_lang('system', 'update_available').'</div>',
				)
			);
		}
	}

	/**
	 * Use this to add language specific labels to template
	 *
	 * @see createHTML()
	 */
	protected function createHTMLLabels() {
		global $type;

		$this->tpl->addTemplateData(
			'main',
			array(
				'title' => strtoupper(psm_get_lang('system', 'title')),
				'subtitle' => psm_get_lang('system', $type),
				'active_' . $type => 'active',
				'label_servers' => psm_get_lang('system', 'servers'),
				'label_users' => psm_get_lang('system', 'users'),
				'label_log' => psm_get_lang('system', 'log'),
				'label_config' => psm_get_lang('system', 'config'),
				'label_update' => psm_get_lang('system', 'update'),
				'label_help' => psm_get_lang('system', 'help'),
			)
		);
	}

	/**
	 * Set a template id that will be added to the main template automatically
	 * once you call the parent::createHTML()
	 *
	 * @param string $tpl_id
	 * @param string $tpl_file if given, the tpl_id will be created automatically from this file
	 * @see getTemplateId() createHTML()
	 */
	public function setTemplateId($tpl_id, $tpl_file = null) {
		$this->tpl_id = $tpl_id;

		if($tpl_file != null) {
			// tpl_file given, try to load the template..
			$this->tpl->newTemplate($tpl_id, $tpl_file);
		}
	}

	/**
	 * Get the mpalte id that will be added to the main template
	 *
	 * @return string
	 * @see setTemplateId()
	 */
	public function getTemplateId() {
		return $this->tpl_id;
	}

	/**
	 * Hide or show the footer of the page
	 * @param boolean $value
	 */
	protected function addFooter($value) {
		$this->add_footer = $value;
	}
}

?>