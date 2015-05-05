<?php
/**
*
* @package Zodiacs
* @copyright (c) 2015 Rich Mcgirr (RMcGirr83)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rmcgirr83\zodiacs\migrations;

class initial_data extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['zodiacs_version']) && version_compare($this->config['zodiacs_version'], '1.0.0', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('zodiacs_version', '1.0.0')),
		);
	}
}
