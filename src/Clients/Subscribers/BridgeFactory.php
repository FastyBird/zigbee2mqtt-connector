<?php declare(strict_types = 1);

/**
 * BridgeFactory.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Clients
 * @since          1.0.0
 *
 * @date           01.01.24
 */

namespace FastyBird\Connector\Zigbee2Mqtt\Clients\Subscribers;

use FastyBird\Connector\Zigbee2Mqtt\Entities;
use FastyBird\Library\Metadata\Documents as MetadataDocuments;

/**
 * Bridge subscriber factory
 *
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Clients
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface BridgeFactory
{

	public function create(MetadataDocuments\DevicesModule\Connector|Entities\Zigbee2MqttConnector $connector): Bridge;

}
