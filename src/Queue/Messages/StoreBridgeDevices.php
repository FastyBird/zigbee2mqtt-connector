<?php declare(strict_types = 1);

/**
 * StoreBridgeDevices.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Queue
 * @since          1.0.0
 *
 * @date           24.12.23
 */

namespace FastyBird\Connector\Zigbee2Mqtt\Queue\Messages;

use FastyBird\Connector\Zigbee2Mqtt\Exceptions;
use Orisai\ObjectMapper;
use Ramsey\Uuid;
use TypeError;
use ValueError;
use function array_map;
use function array_merge;

/**
 * Bridge group description message
 *
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Queue
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class StoreBridgeDevices extends Bridge implements Message
{

	/**
	 * @param array<DeviceDescription> $devices
	 */
	public function __construct(
		Uuid\UuidInterface $connector,
		string $baseTopic,
		#[ObjectMapper\Rules\ArrayOf(
			new ObjectMapper\Rules\MappedObjectValue(class: DeviceDescription::class),
			new ObjectMapper\Rules\IntValue(unsigned: true),
		)]
		private readonly array $devices,
	)
	{
		parent::__construct($connector, $baseTopic);
	}

	/**
	 * @return array<DeviceDescription>
	 */
	public function getDevices(): array
	{
		return $this->devices;
	}

	/**
	 * @throws Exceptions\InvalidState
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function toArray(): array
	{
		return array_merge(
			parent::toArray(),
			[
				'devices' => array_map(
					static fn (DeviceDescription $device): array => $device->toArray(),
					$this->getDevices(),
				),
			],
		);
	}

}
