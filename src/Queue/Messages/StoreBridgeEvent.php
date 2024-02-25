<?php declare(strict_types = 1);

/**
 * StoreBridgeEvent.php
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

use FastyBird\Connector\Zigbee2Mqtt\Types;
use Orisai\ObjectMapper;
use Ramsey\Uuid;
use function array_merge;

/**
 * Bridge event message
 *
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Queue
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class StoreBridgeEvent extends Bridge implements Message
{

	public function __construct(
		Uuid\UuidInterface $connector,
		string $baseTopic,
		#[ObjectMapper\Rules\BackedEnumValue(class: Types\BridgeEvent::class)]
		private readonly Types\BridgeEvent $type,
		#[ObjectMapper\Rules\MappedObjectValue(EventData::class)]
		private readonly EventData $data,
	)
	{
		parent::__construct($connector, $baseTopic);
	}

	public function getType(): Types\BridgeEvent
	{
		return $this->type;
	}

	public function getData(): EventData
	{
		return $this->data;
	}

	public function toArray(): array
	{
		return array_merge(
			parent::toArray(),
			[
				'type' => $this->getType()->value,
				'data' => $this->getData()->toArray(),
			],
		);
	}

}
