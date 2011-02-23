<?php if (! defined('EXT')) exit('Invalid file request');


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
	var $description    = 'Adds a &ldquo;Templates&lrquo; Link Type to Wygwam&rsquo;s Link dialog';
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
		global $EXT, $FNS, $DB, $PREFS, $LANG;

		// If another extension shares the same hook,
		// we need to get the latest and greatest config
		if ($EXT->last_call !== FALSE)
		{
			$config = $EXT->last_call;
		}

		$site_id  = $PREFS->ini('site_id');
		$site_url = $PREFS->ini('site_url');
		$templates_str = $LANG->line('design');

		$query = $DB->query('SELECT t.template_name, tg.group_name
		                     FROM exp_templates t, exp_template_groups tg
		                     WHERE t.group_id = tg.group_id
		                           AND t.site_id = '.$site_id.'
		                     ORDER BY tg.group_name, t.template_name');

		if ($query->num_rows)
		{
			$group = '';

			foreach ($query->result as $tmpl)
			{
				// are we starting a new group?
				if ($tmpl['group_name'] != $group)
				{
					$group = $tmpl['group_name'];

					$url = $FNS->create_page_url($site_url, $tmpl['group_name']);
					$config['link_types'][$templates_str][] = array('label' => $tmpl['group_name'], 'url' => $url);
				}

				// skip the index template
				if ($tmpl['template_name'] == 'index') continue;

				// add the template
				$uri = $tmpl['group_name'].'/'.$tmpl['template_name'];
				$url = $FNS->create_page_url($site_url, $uri);
				$config['link_types'][$templates_str][] = array('label' => $uri, 'url' => $url);
			}
		}

		return $config;
	}
}
