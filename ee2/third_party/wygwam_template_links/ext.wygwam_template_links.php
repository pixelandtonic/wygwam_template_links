<?php if (! defined('APP_VER')) exit('No direct script access allowed');


/**
 * Wygwam Template Links
 * 
 * @author    Brad Bell <brad@pixelandtonic.com>
 * @copyright Copyright (c) 2011 Pixel & Tonic, Inc
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */
class Wygwam_template_links_ext {

	var $name           = 'Wygwam Template Links';
	var $version        = '1.0';
	var $description    = 'Adds a “Site Templates” Link Type to Wygwam’s Link dialog';
	var $settings_exist = 'n';
	var $docs_url       = 'http://github.com/brandonkelly/wygwam_template_link';

	/**
	 * Class Constructor
	 */
	function __construct()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Activate Extension
	 */
	function activate_extension()
	{
		// add the row to exp_extensions
		$this->EE->db->insert('extensions', array(
			'class'    => get_class($this),
			'method'   => 'wygwam_config',
			'hook'     => 'wygwam_config',
			'settings' => '',
			'priority' => 10,
			'version'  => $this->version,
			'enabled'  => 'y'
		));
	}

	/**
	 * Update Extension
	 */
	function update_extension($current = '')
	{
		// Nothing to change...
		return FALSE;
	}

	/**
	 * Disable Extension
	 */
	function disable_extension()
	{
		// Remove all Wygwam_template_links_ext rows from exp_extensions
		$this->EE->db->where('class', get_class($this))
		             ->delete('extensions');
	}

	// --------------------------------------------------------------------

	/**
	 * wygwam_config hook
	 */
	function wygwam_config($config, $settings)
	{
		// If another extension shares the same hook,
		// we need to get the latest and greatest config
		if ($this->EE->extensions->last_call !== FALSE)
		{
			$config = $this->EE->extensions->last_call;
		}

		$site_id = $this->EE->config->item('site_id');

		$query = $this->EE->db->query('SELECT t.template_name, tg.group_name
		                               FROM exp_templates t, exp_template_groups tg
		                               WHERE t.group_id = tg.group_id
		                               AND t.site_id = '.$site_id);

		if ($query->num_rows())
		{
			foreach ($query->result_array() as $entry)
			{
				$template_name = $entry['template_name'];
				$template_name = $template_name == 'index' ? '' : $template_name;

				$full_url = rtrim($this->EE->functions->create_page_url($this->EE->config->item('site_url'), $entry['group_name'].'/'.$template_name), '/');
				$config['link_types']['Site Templates'][] = array(
					'label'    => $full_url,
					'url'      => $full_url
				);
			}
		}

		return $config;
	}
}
