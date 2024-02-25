<?php declare(strict_types = 1);

/**
 * FindBridgeDevices.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Queries
 * @since          1.0.0
 *
 * @date           25.12.23
 */

namespace FastyBird\Connector\Zigbee2Mqtt\Queries\Configuration;

use FastyBird\Connector\Zigbee2Mqtt\Documents;

/**
 * Find bridge entities query
 *
 * @template T of Documents\Devices\Bridge
 * @extends  FindDevices<T>
 *
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Queries
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class FindBridgeDevices extends FindDevices
{

}
