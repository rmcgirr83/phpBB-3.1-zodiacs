<?php
/**
*
* @package Zodiacs
* @copyright (c) 2015 Rich Mcgirr (RMcGirr83)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rmcgirr83\zodiacs\migrations;

class revert_data extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return empty($this->config['zodiacs_version']);
	}

	static public function depends_on()
	{
		return array('\rmcgirr83\zodiacs\migrations\initial_data');
	}

	public function update_data()
	{
		return array(
			array('config.remove', array('zodiacs_version')),
		);
	}
}
