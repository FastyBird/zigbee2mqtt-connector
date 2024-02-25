<?php declare(strict_types = 1);

/**
 * StoreDeviceState.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Queue
 * @since          1.0.0
 *
 * @date           25.12.23
 */

namespace FastyBird\Connector\Zigbee2Mqtt\Queue\Messages;

use Orisai\ObjectMapper;
use Ramsey\Uuid;
use function array_map;
use function array_merge;

/**
 * Device state description message
 *
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Queue
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class StoreDeviceState extends Device implements Message
{

	/**
	 * @param array<SingleExposeData|CompositeExposeData> $states
	 */
	public function __construct(
		Uuid\UuidInterface $connector,
		string $baseTopic,
		string $device,
		#[ObjectMapper\Rules\ArrayOf(
			new ObjectMapper\Rules\AnyOf([
				new ObjectMapper\Rules\MappedObjectValue(class: SingleExposeData::class),
				new ObjectMapper\Rules\MappedObjectValue(class: CompositeExposeData::class),
			]),
			new ObjectMapper\Rules\IntValue(unsigned: true),
		)]
		private readonly array $states,
	)
	{
		parent::__construct($connector, $baseTopic, $device);
	}

	/**
	 * @return array<SingleExposeData|CompositeExposeData>
	 */
	public function getStates(): array
	{
		return $this->states;
	}

	public function toArray(): array
	{
		return array_merge(
			parent::toArray(),
			[
				'states' => array_map(
					static fn (SingleExposeData|CompositeExposeData $item): array => $item->toArray(),
					$this->getStates(),
				),
			],
		);
	}

}
