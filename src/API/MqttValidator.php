<?php declare(strict_types = 1);

/**
 * MqttValidator.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     API
 * @since          1.0.0
 *
 * @date           24.02.20
 */

namespace FastyBird\Connector\Zigbee2Mqtt\API;

use Nette;
use function preg_match;

/**
 * MQTT topic validator
 *
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     API
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class MqttValidator
{

	use Nette\SmartObject;

	// TOPIC: /zigbee2mqtt/*
	public const TOPIC_REGEXP = '/^zigbee2mqtt\/.*$/';

	// TOPIC: /zigbee2mqtt/<device>
	public const DEVICE_REGEXP = '/(?i)^(?<base_topic>[a-z0-9_-]+)\/(?<name>([a-z0-9-_\/]+))$/';

	// TOPIC: /zigbee2mqtt/<device>(/<availability|get>)
	public const DEVICE_WITH_ACTION_REGEXP = '/(?i)^(?<base_topic>[a-z0-9_-]+)\/(?<name>([a-z0-9-_\/]+))(\/(?<type>(availability|get))){1}$/';

	// TOPIC: /zigbee2mqtt/bridge/<info|state|devices|groups|event|extensions>
	public const BRIDGE_REGEXP = '/(?i)^(?<base_topic>[a-z0-9_-]+)\/bridge\/(?<type>(info|state|logging|devices|groups|event|extensions))$/';
	// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
	public const BRIDGE_REQUEST_RESPONSE_REGEXP = '/(?i)^(?<base_topic>[a-z0-9_-]+)\/bridge\/(?<type>(request|response))\/(?<request_response>(permit_join|health_check|coordinator_check|restart|networkmap|extension\/save|backup|install_code\/add|device\/remove|device\/ota_update\/check|device\/ota_update\/update|device\/configure|device\/options|device\/rename|device\/bind|device\/unbind|device\/configure_reporting|group\/remove|group\/add|group\/rename|group\/options|group\/members\/add|group\/members\/remove|group\/members\/remove_all|options|config\/last_seen|config\/elapsed|config\/log_level|config\/homeassistant|touchlink\/factory_reset|touchlink\/scan|touchlink\/identify))$/';

	public static function validate(string $topic): bool
	{
		return self::validateTopic($topic) && (
			self::validateDevice($topic)
			|| self::validateBridge($topic)
		);
	}

	public static function validateTopic(string $topic): bool
	{
		return preg_match(self::TOPIC_REGEXP, $topic) === 1;
	}

	public static function validateDevice(string $topic): bool
	{
		return preg_match(self::DEVICE_REGEXP, $topic) === 1
			|| preg_match(self::DEVICE_WITH_ACTION_REGEXP, $topic) === 1;
	}

	public static function validateBridge(string $topic): bool
	{
		return preg_match(self::BRIDGE_REGEXP, $topic) === 1
			|| preg_match(self::BRIDGE_REQUEST_RESPONSE_REGEXP, $topic) === 1;
	}

}
