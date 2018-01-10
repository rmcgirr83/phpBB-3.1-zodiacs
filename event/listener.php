<?php
/**
*
* Zodiacs extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Rich McGirr (RMcGirr83)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rmcgirr83\zodiacs\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/**
	* the path to the images directory
	*
	*@var string
	*/
	protected $zodiacs_path;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user)
	{
		$this->config = $config;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.viewtopic_cache_user_data'			=> 'viewtopic_cache_user_data',
			'core.viewtopic_cache_guest_data'			=> 'viewtopic_cache_guest_data',
			'core.viewtopic_modify_post_row'			=> 'viewtopic_modify_post_row',
			'core.memberlist_view_profile'				=> 'memberlist_view_profile',
			'core.search_get_posts_data'				=> 'search_get_posts_data',
			'core.search_modify_tpl_ary'				=> 'search_modify_tpl_ary',
			'core.user_setup'							=> 'user_setup',
		);
	}

	/**
	* Set up the the lang vars
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function user_setup($event)
	{
		if (!$this->birthdays_allowed())
		{
			return;
		}

		// what page are we on?
		$page_name = substr($this->user->page['page_name'], 0, strpos($this->user->page['page_name'], '.'));

		// We only care about memberlist and viewtopic
		if (in_array($page_name, array('viewtopic', 'memberlist', 'search')))
		{
			$lang_set_ext = $event['lang_set_ext'];
			$lang_set_ext[] = array(
				'ext_name' => 'rmcgirr83/zodiacs',
				'lang_set' => 'zodiacs',
			);
			$this->template->assign_vars(array(
				'S_ZODIACS' => true,
			));
			$event['lang_set_ext'] = $lang_set_ext;
		}
	}

	/**
	* Update viewtopic user data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_cache_user_data($event)
	{
		if (!$this->birthdays_allowed())
		{
			return;
		}

		$array = $event['user_cache_data'];
		$array['user_birthday'] = $event['row']['user_birthday'];
		$event['user_cache_data'] = $array;
	}

	/**
	* Update viewtopic guest data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_cache_guest_data($event)
	{
		if (!$this->birthdays_allowed())
		{
			return;
		}

		$array = $event['user_cache_data'];
		$array['user_birthday'] = '';
		$event['user_cache_data'] = $array;
	}
	/**
	* Modify the viewtopic post row
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_modify_post_row($event)
	{
		if (!$this->birthdays_allowed())
		{
			return;
		}

		$zodiac = $this->get_user_zodiac($event['user_poster_data']['user_birthday']);

		$event['post_row'] = array_merge($event['post_row'],array(
			'USER_ZODIAC' => $zodiac,
		));
	}

	/**
	* Display zodiac on viewing user profile
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function memberlist_view_profile($event)
	{
		if (!$this->birthdays_allowed())
		{
			return;
		}

		$zodiac = $this->get_user_zodiac($event['member']['user_birthday']);

		$this->template->assign_vars(array(
			'USER_ZODIAC'	=> $zodiac,
		));
	}

	/**
	* Get birthday on search
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function search_get_posts_data($event)
	{
		if (!$this->birthdays_allowed())
		{
			return;
		}

		$array = $event['sql_array'];
		$array['SELECT'] .= ', u.user_birthday';
		$event['sql_array'] = $array;
	}

	/**
	* Display zodiac on search
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function search_modify_tpl_ary($event)
	{
		if (!$this->birthdays_allowed())
		{
			return;
		}

		if ($event['show_results'] == 'topics')
		{
			return;
		}

		$array = $event['tpl_ary'];
		$zodiac = $this->get_user_zodiac($event['row']['user_birthday']);
		$array = array_merge($array, array(
			'USER_ZODIAC'	=> $zodiac,
		));

		$event['tpl_ary'] = $array;
	}

	/**
	 * Get user zodiac
	 *
	 * @author RMcGirr83
	 * @param string $user_birthday User's Birthday
	 * @return string Zodiac image
	 */
	private function get_user_zodiac($user_birthday)
	{
		if (!empty($user_birthday))
		{
			list($bday, $bmonth) = array_map('intval', explode('-', $user_birthday));

			$zodiac_array = array(
				'aries'		=> array(3, 20, 4, 20),
				'taurus'	=> array(4, 19, 5, 21),
				'gemini'	=> array(5, 20, 6, 21),
				'cancer'	=> array(6, 20, 7, 23),
				'leo'		=> array(7, 22, 8, 23),
				'virgo'		=> array(8, 22, 9, 23),
				'libra'		=> array(9, 22, 10, 23),
				'scorpio'	=> array(10, 22, 11, 22),
				'sagittarius'	=> array(11, 21, 12, 22),
				'capricorn'	=> array(12, 21, 1, 20),
				'aquarius'	=> array(1, 19, 2, 19),
				'pisces'	=> array(2, 18, 3, 21),
			);

			foreach ($zodiac_array as $sign => $date)
			{
				if (($bmonth == $date[0] && $bday > $date[1]) || ($bmonth == $date[2] && $bday < $date[3]))
				{
					$title = $this->user->lang(strtoupper($sign));
					//return "<img src='$image' alt='$title' title='$title' style='vertical-align:middle;' />";
					return '<i class="ai ' . $sign . '" title="' . $title . '"></i>';
				}
			}
		}
	}

	/**
	 * Ensure loading of birthdays and allowing of birthdays is set
	**/
	private function birthdays_allowed()
	{
		return $this->config['allow_birthdays'];
	}
}
