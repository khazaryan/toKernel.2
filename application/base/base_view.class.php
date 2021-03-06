<?php
/**
 * toKernel - Universal PHP Framework.
 * Extended Base View class for addons and modules.
 *
 * This file is part of toKernel.
 *
 * toKernel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * toKernel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with toKernel. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   base
 * @package    application
 * @subpackage base
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2016 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 2.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * addon base_view
 *
 * @author David A. <tokernel@gmail.com>
 */
class base_view extends view {

	/**
	 * Class constructor
	 *
	 * @access public
	 * @param string $file
	 * @param array $params
	 * @param array $vars
	 * @return void
	 */
	public function __construct($file, $params, $vars) {
		parent::__construct($file, $params, $vars);
	}

} // End class base_view

// End of file
?>