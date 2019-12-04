<?php

namespace Financer\FilterSlider\Slider;

/**
 * Class BadCreditHistoryLoan
 * @package Financer\FilterSlider\Slider
 */
/**
 * Class BadCreditHistoryLoan
 * @package Financer\FilterSlider\Slider
 */
class BadCreditHistoryLoan extends GenericLoan {

	/**
	 * @var array
	 */
	protected $filters = [ 'bad_history' ];

	protected $filtersEnabled = [ 'bad_history' ];

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	public function generateJsMaps( $params = [] ): array {

		return parent::generateJsMaps(
			[
				'where' => [
					[
						'key'   => 'company_parent.d.bad_history',
						'value' => '1',
					],
				],
			]
		);
	}

	/**
	 *
	 */
	protected function buildQuery() {

		parent::buildQuery();
		$this->query['where'][] = [
			'key'   => 'company_parent.d.bad_history',
			'value' => '1',
		];
	}
}
