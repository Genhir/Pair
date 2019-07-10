<?php

namespace Pair;

class Menu {

	/**
	 * Item object list.
	 * @var array
	 */
	protected $items = array();

	/**
	 * Adds a single-item menu entry. The optional badge can be a subtitle. 
	 * 
	 * @param	string	Url of item.
	 * @param	string	Title shown.
	 * @param	string	Optional, can be an icon, a subtitle or icon placeholder.
	 * @param	string	Optional, is CSS extra class definition.
	 * @param	string	Optional, the anchor target.
	 */
	public function addItem(string $url, string $title, string $badge=NULL, string $class=NULL, string $target=NULL) {

		$this->items[]	= self::getItemObject($url, $title, $badge, $class, $target);

	}

	/**
	 * Adds a multi-entry menu item. The list is array of single-item objects.
	 * 
	 * @param	string	Title for this item.
	 * @param	array	List of single-item objects
	 * @param	string	Optional, can be an icon, a subtitle or icon placeholder.
	 */
	public function addMulti(string $title, array $list, string $class=NULL) {
	
		$multi 			= new \stdClass();
		$multi->type	= 'multi';
		$multi->title	= $title;
		$multi->class	= $class;
		$multi->list	= array();
		foreach ($list as $i) {
			$multi->list[]	= $i;
		}

		$this->items[] = $multi;

	}
	
	/**
	 * Adds a separator to menu.
	 * 
	 * @param	string	Separator title. Optional.
	 */
	public function addSeparator(string $title=NULL) {
	
		$item 			= new \stdClass();
		$item->type		= 'separator';
		$item->title	= $title;
		$this->items[]	= $item;
	
	}
	
	/**
	 * Static method utility to create single-item object.
	 *  
	 * @param	string	Url of item.
	 * @param	string	Title shown.
	 * @param	string	Optional, can be an icon, a subtitle or icon placeholder.
	 * @param	string	Optional, is CSS extra class definition.
	 * @param	string	Optional, the anchor target.
	 * 
	 * @return	stdClass
	 */
	public static function getItemObject(string $url, string $title, string $badge=NULL, string $class=NULL, string $target=NULL): \stdClass {

		$item 			= new \stdClass();
		$item->type		= 'single';
		$item->url		= $url;
		$item->title	= $title;
		$item->badge	= $badge;
		$item->class	= $class;
		$item->target	= $target;

		return $item;

	}

	/**
	 * Builds HTML of this menu.
	 *
	 * @return string
	 */
	public function render(): string {

		$app = Application::getInstance();

		$ret = '';

		foreach ($this->items as $item) {
			
			// check on permissions
			if (isset($item->url) and (!is_a($app->currentUser, 'Pair\User') or !$app->currentUser->canAccess($item->url))) {
				continue;
			}
			
			switch ($item->type) {
				
				// single menu item rendering
				case 'single':

					$class  = ($item->url == $app->activeMenuItem ? ' active' : '');

					if ($item->class) {
						$class .= ' ' . $item->class;
					}

					// if url set <a>, otherwise set <div>
					$ret .= $item->url ? '<a href="' . $item->url . '"' : '<div'; 
					
					$ret .= ' class="item' . $class . '"' .
						($item->target ? ' target="' . $item->target . '"' : '') .
						(!is_null($item->badge) ? ' data-badge="' . (int)$item->badge . '" ' : '') .
						'>' .
						(!is_null($item->badge) ? '<span class="badge">' . $item->badge . '</span>' : '') .
						'<span class="title">' . $item->title . '</span>';

					// if url close </a>, otherwise close </div>
					$ret .= $item->url ? '</a>' : '</div>'; 
					break;

				// menu item with many sub-items rendering
				case 'multi':

					$links		= '';
					$menuClass	= '';

					// builds each sub-item link
					foreach ($item->list as $i) {

						// check on permissions
						if (isset($i->url) and !(is_a($app->currentUser, 'Pair\User') and !$app->currentUser->canAccess($i->url))) {
							continue;
						}
						
						if ($i->url == $app->activeMenuItem) {
							$class		= ' active';
							$menuClass	= ' open';
						} else {
							$class		= '';
						}

						if ($i->class) {
							$class .= ' ' . $i->class;
						}

						$links .= '<a class="item' . $class . '" href="' . $i->url . '">' .
							(!is_null($i->badge) ? '<span class="badge">' . $i->badge . '</span>' : '') .
							'<span class="title">' . $i->title . '</span>' .
							'</a>';
					}
					
					// prevent empty multi-menu
					if ('' == $links) {
						break;
					}
					
					// assembles the multi-menu
					$ret .= '<div class="dropDownMenu' . $menuClass . '">' .
						'<div class="title">' . $item->title . '</div>' .
						'<div class="itemGroup">' . $links . '</div></div>';
					break;
					
				case 'separator':

					if (!$item->title) $item->title = '&nbsp;';
					$ret .= '<div class="separator">' . $item->title . '</div>';
					break;

			}

		}

		return $ret;

	}

}
