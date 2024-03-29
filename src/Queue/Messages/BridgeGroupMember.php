<?php declare(strict_types = 1);

/**
 * BridgeGroupMember.php
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

use Orisai\ObjectMapper;

/**
 * Bridge group member description message
 *
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Queue
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final readonly class BridgeGroupMember implements Message
{

	public function __construct(
		#[ObjectMapper\Rules\StringValue(notEmpty: true)]
		#[ObjectMapper\Modifiers\FieldName('ieee_address')]
		private string $ieeeAddress,
		#[ObjectMapper\Rules\IntValue(unsigned: true)]
		private int $endpoint,
	)
	{
	}

	public function getIeeeAddress(): string
	{
		return $this->ieeeAddress;
	}

	public function getEndpoint(): int
	{
		return $this->endpoint;
	}

	public function toArray(): array
	{
		return [
			'ieee_address' => $this->getIeeeAddress(),
			'endpoint' => $this->getEndpoint(),
		];
	}

}
