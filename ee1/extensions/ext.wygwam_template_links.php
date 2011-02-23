<?php

if ( ! defined('EXT')) exit('Invalid file request');
/**
 * Wygwam Template Links
 *
 * @author    Brad Bell <brad@pixelandtonic.com>
 * @copyright Copyright (c) 2011 Pixel & Tonic, Inc
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */

class Wygwam_template_links {

	var $name           = 'Wygwam Template Links';
	var $version        = '1.0';
	var $description    = 'Adds a "Site Template" Link Type to Wygwam\'s Link dialog';
	var $settings_exist = 'n';
	var $docs_url       = 'http://github.com/brandonkelly/wygwam_template_links';

	// --------------------------------------------------------------------

	/**
	 * Activate Extension
	 */
	function activate_extension()
	{
		global $DB;

		// add the row to exp_extensions
		$DB->query($DB->insert_string('exp_extensions', array(
			'class'    => get_class($this),
			'method'   => 'wygwam_config',
			'hook'     => 'wygwam_config',
			'settings' => '',
			'priority' => 10,
			'version'  => $this->version,
			'enabled'  => 'y'
		)));
	}

	/**
	 * Update Extension
	 *
	 * @param string  $current  Previous installed version of the extension
	 */
	function update_extension($current='')
	{
		// Nothing to change...
		return FALSE;
	}

	/**
	 * Disable Extension
	 */
	function disable_extension()
	{
		global $DB;
		$DB->query($DB->update_string('exp_extensions', array('enabled' => 'n'), 'class = "'.get_class($this).'"'));
	}

	// --------------------------------------------------------------------

	/**
	 * wygwam_config hook
	 */
	function wygwam_config($config, $settings)
	{
		global $EXT, $FNS, $DB, $PREFS;

		// If another extension shares the same hook,
		// we need to get the latest and greatest config
		if ($EXT->last_call !== FALSE)
		{
			$config = $EXT->last_call;
		}

		$site_id = $PREFS->ini('site_id');

		$query = $DB->query('SELECT t.template_name, tg.group_name
							 FROM exp_templates t, exp_template_groups tg
							 WHERE t.group_id = tg.group_id
							 AND t.site_id = '.$site_id);

		if ($query->num_rows > 0)
		{
			foreach ($query->result as $entry)
			{
				$template_name = $entry['template_name'];
				$template_name = $template_name == 'index' ? '' : $template_name;

				$full_url = rtrim($FNS->create_page_url($PREFS->ini('site_url'), $entry['group_name'].'/'.$template_name), '/');
				$config['link_types']['Site Template'][] = array(
					'label'    => $full_url,
					'url'      => $full_url
				);

			}
		}

		return $config;
	}
}