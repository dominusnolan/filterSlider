<?php

namespace Financer\FilterSlider\Slider;

/**
 * Class BadCreditHistoryLoan
 * @package Financer\FilterSlider\Slider
 */
class LoanBrokers extends GenericLoan {
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
						'key'   => 'company_parent.d.loan_broker',
						'value' => '1',
					],
				],
			]
		);
	}

	/**
	 * @return void
	 */
	protected function buildQuery() {

		parent::buildQuery();
		$this->query['where'][] = [
			'key'   => 'company_parent.d.loan_broker',
			'value' => '1',

		];
	}
}
