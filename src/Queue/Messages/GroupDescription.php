<?php declare(strict_types = 1);

/**
 * GroupDescription.php
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
use function array_map;

/**
 * Group description message
 *
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Queue
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final readonly class GroupDescription implements Message
{

	/**
	 * @param array<Scene> $scenes
	 * @param array<BridgeGroupMember> $members
	 */
	public function __construct(
		#[ObjectMapper\Rules\IntValue(unsigned: true)]
		private int $id,
		#[ObjectMapper\Rules\StringValue(notEmpty: true)]
		#[ObjectMapper\Modifiers\FieldName('friendly_name')]
		private string $friendlyName,
		#[ObjectMapper\Rules\ArrayOf(
			new ObjectMapper\Rules\MappedObjectValue(class: Scene::class),
			new ObjectMapper\Rules\IntValue(unsigned: true),
		)]
		private array $scenes,
		#[ObjectMapper\Rules\ArrayOf(
			new ObjectMapper\Rules\MappedObjectValue(class: BridgeGroupMember::class),
			new ObjectMapper\Rules\IntValue(unsigned: true),
		)]
		private array $members,
	)
	{
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getFriendlyName(): string
	{
		return $this->friendlyName;
	}

	/**
	 * @return array<Scene>
	 */
	public function getScenes(): array
	{
		return $this->scenes;
	}

	/**
	 * @return array<BridgeGroupMember>
	 */
	public function getMembers(): array
	{
		return $this->members;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'friendly_name' => $this->getFriendlyName(),
			'scenes' => array_map(
				static fn (Scene $scene): array => $scene->toArray(),
				$this->getScenes(),
			),
			'members' => array_map(
				static fn (BridgeGroupMember $member): array => $member->toArray(),
				$this->getMembers(),
			),
		];
	}

}
