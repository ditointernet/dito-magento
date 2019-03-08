<?php

namespace Dito\Magento2module\Block;

class Magento2module extends Template
{
  	/** @var array */
	protected $customerData = array();

	public function __construct(
		Template\Context $context,
		array $data
	) {
		parent::__construct($context, $data);
	}
}