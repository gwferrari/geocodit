<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocodit\Gateway;

interface  GatewayInterface {
    public function __construct($name, $sourceUrl);
	public function getName();
	public function getSource();
	public function getStream();
}