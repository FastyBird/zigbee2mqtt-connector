<?php declare(strict_types = 1);

/**
 * StoreDeviceConnectionState.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Entities
 * @since          1.0.0
 *
 * @date           31.12.23
 */

namespace FastyBird\Connector\Zigbee2Mqtt\Entities\Messages;

use FastyBird\Connector\Zigbee2Mqtt\Types;
use FastyBird\Library\Bootstrap\ObjectMapper as BootstrapObjectMapper;
use Ramsey\Uuid;
use function array_merge;

/**
 * Store device connection state message entity
 *
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class StoreDeviceConnectionState extends Device implements Entity
{

	public function __construct(
		Uuid\UuidInterface $connector,
		string $baseTopic,
		string $identifier,
		#[BootstrapObjectMapper\Rules\ConsistenceEnumValue(class: Types\ConnectionState::class)]
		private readonly Types\ConnectionState $state,
	)
	{
		parent::__construct($connector, $baseTopic, $identifier);
	}

	public function getState(): Types\ConnectionState
	{
		return $this->state;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'state' => $this->getState()->getValue(),
		]);
	}

}
