<?php
namespace Financer\FilterSlider\Interfaces;

use Financer\FilterSlider\Abstracts\Slider;

/**
 * Interface TableInterface
 * @package Financer\FilterSlider\Interfaces
 */
interface TableInterface {
	/**
	 * @param null|\Pods $pod
	 *
	 * @param Slider     $slider
	 *
	 * @return void
	 * @internal param null $postType
	 *
	 * @internal param array $query
	 */
	public static function build( \Pods $pod, Slider $slider = null );
}
