<?php
/**
 * toKernel - Universal PHP Framework.
 * array processing class
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
 * @category   library
 * @package    framework
 * @subpackage library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2016 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.4.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * array_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class array_lib {

	/**
	 * Check, is associative array
	 *
	 * @access public
	 * @param array $arr
	 * @return bool
	 */
	public function is_assoc($arr) {
		return array_keys($arr) !== range(0, count($arr) - 1);
	} // end func is_assoc

	/**
	 * Rename key in array
	 *
	 * @access public
	 * @param string $ext_key
	 * @param string $new_key
	 * @param array $arr
	 * @return array | bool
	 */
	public function array_key_rename($ext_key, $new_key, $arr) {

		if(!is_array($arr)) {
			return false;
		}

		$new_arr = array();

		foreach($arr as $key => $value) {
			if($key == $ext_key and is_string($key)) {
				$key = $new_key;
			}

			$new_arr[$key] = $value;
		} // end foreach

		return $new_arr;

	} // end func array_key_rename

	/**
	 * Return position in array by key
	 *
	 * @access public
	 * @param mixed $needle
	 * @param array $array
	 * @return int | bool
	 */
	function array_key_pos($needle, $array) {

		if(!is_array($array) or is_null($needle)) {
			return false;
		}

		$tmp = array_keys($array);
		$index = array_search($needle, $tmp);

		if($index !== false) {
			return $index + 1;
		} else {
			return false;
		}

	} // end func array_key_pos

	/**
	 * Check if all values of array elements is empty
	 *
	 * @access public
	 * @param array $array
	 * @return bool
	 */
	public function is_elements_empty($array) {

		if(empty($array)) {
			return true;
		}

		$tmp = '';

		foreach($array as $key => $value) {
			if(!is_array($value)) {
				$tmp .= $value;
			} else {
				if(!empty($value)) {
					return false;
				}
			}
		}

		if($tmp == '') {
			return true;
		} else {
			return false;
		}

	} // End func is_elements_empty

	/* End of class array_lib */
}

/* End of file */
?>