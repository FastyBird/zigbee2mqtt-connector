<?php declare(strict_types = 1);

/**
 * SwitchType.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Queue
 * @since          1.0.0
 *
 * @date           02.01.24
 */

namespace FastyBird\Connector\Zigbee2Mqtt\Queue\Messages\Exposes;

use FastyBird\Connector\Zigbee2Mqtt\Types;
use Orisai\ObjectMapper;
use TypeError;
use ValueError;
use function array_map;
use function array_merge;

/**
 * Switch type expose type message
 *
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Queue
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class SwitchType extends Type
{

	/**
	 * @param array<BinaryType|EnumType|NumericType|TextType|CompositeType> $features
	 */
	public function __construct(
		#[ObjectMapper\Rules\ArrayEnumValue(cases: [Types\ExposeType::SWITCH->value])]
		private readonly string $type,
		#[ObjectMapper\Rules\ArrayOf(
			new ObjectMapper\Rules\AnyOf([
				new ObjectMapper\Rules\MappedObjectValue(class: BinaryType::class),
				new ObjectMapper\Rules\MappedObjectValue(class: EnumType::class),
				new ObjectMapper\Rules\MappedObjectValue(class: NumericType::class),
				new ObjectMapper\Rules\MappedObjectValue(class: TextType::class),
				new ObjectMapper\Rules\MappedObjectValue(class: CompositeType::class),
			]),
			new ObjectMapper\Rules\IntValue(unsigned: true),
		)]
		private readonly array $features,
	)
	{
		parent::__construct();
	}

	/**
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function getType(): Types\ExposeType
	{
		return Types\ExposeType::from($this->type);
	}

	/**
	 * @return array<BinaryType|EnumType|NumericType|TextType|CompositeType>
	 */
	public function getFeatures(): array
	{
		return $this->features;
	}

	public function toArray(): array
	{
		return array_merge(
			parent::toArray(),
			[
				'features' => array_map(static fn (Type $expose): array => $expose->toArray(), $this->getFeatures()),
			],
		);
	}

}
