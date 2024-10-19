<?php declare(strict_types = 1);

/**
 * V1Parser.php
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

use FastyBird\Connector\Zigbee2Mqtt\Exceptions;
use Nette;
use Ramsey\Uuid;
use function array_key_exists;
use function assert;
use function preg_match;
use function strtolower;

/**
 * MQTT topic parser
 *
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     API
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class MqttParser
{

	use Nette\SmartObject;

	/**
	 * @return array<string, mixed>
	 *
	 * @throws Exceptions\ParseMessage
	 * @throws Exceptions\InvalidArgument
	 */
	public static function parse(
		Uuid\UuidInterface $connector,
		string $topic,
		string $payload,
	): array
	{
		if (!MqttValidator::validate($topic)) {
			throw new Exceptions\ParseMessage('Provided topic is not valid');
		}

		if (MqttValidator::validateBridge($topic)) {
			return self::parseBridgeMessage($connector, $topic, $payload);
		}

		if (MqttValidator::validateDevice($topic)) {
			return self::parseDeviceMessage($connector, $topic, $payload);
		}

		throw new Exceptions\ParseMessage('Provided topic is not valid');
	}

	/**
	 * @return array<string, Uuid\UuidInterface|string>
	 *
	 * @throws Exceptions\InvalidArgument
	 */
	private static function parseBridgeMessage(
		Uuid\UuidInterface $connector,
		string $topic,
		string $payload,
	): array
	{
		if (preg_match(MqttValidator::BRIDGE_REGEXP, $topic) === 1) {
			preg_match(MqttValidator::BRIDGE_REGEXP, $topic, $matches);
			assert(array_key_exists('type', $matches));
			assert(array_key_exists('base_topic', $matches));

			return [
				'connector' => $connector,
				'base_topic' => $matches['base_topic'],
				'type' => $matches['type'],
				'payload' => $payload,
			];
		} elseif (preg_match(MqttValidator::BRIDGE_REQUEST_RESPONSE_REGEXP, $topic) === 1) {
			preg_match(MqttValidator::BRIDGE_REQUEST_RESPONSE_REGEXP, $topic, $matches);
			assert(array_key_exists('type', $matches));
			assert(array_key_exists('request_response', $matches));
			assert(array_key_exists('base_topic', $matches));

			return [
				'connector' => $connector,
				'base_topic' => $matches['base_topic'],
				$matches['type'] => $matches['request_response'],
				'payload' => $payload,
			];
		}

		throw new Exceptions\InvalidArgument('Provided unsupported topic to parse');
	}

	/**
	 * @return array<string, Uuid\UuidInterface|string|null>
	 *
	 * @throws Exceptions\InvalidArgument
	 */
	private static function parseDeviceMessage(
		Uuid\UuidInterface $connector,
		string $topic,
		string $payload,
	): array
	{
		if (preg_match(MqttValidator::DEVICE_WITH_ACTION_REGEXP, $topic) === 1) {
			preg_match(MqttValidator::DEVICE_WITH_ACTION_REGEXP, $topic, $matches);
			assert(array_key_exists('base_topic', $matches));
			assert(array_key_exists('name', $matches));
			assert(array_key_exists('type', $matches));

			return [
				'connector' => $connector,
				'device' => $matches['name'],
				'base_topic' => strtolower($matches['base_topic']),
				'type' => strtolower($matches['type']),
				'payload' => $payload,
			];
		} elseif (preg_match(MqttValidator::DEVICE_REGEXP, $topic) === 1) {
			preg_match(MqttValidator::DEVICE_REGEXP, $topic, $matches);
			assert(array_key_exists('base_topic', $matches));
			assert(array_key_exists('name', $matches));

			return [
				'connector' => $connector,
				'device' => $matches['name'],
				'base_topic' => $matches['base_topic'],
				'payload' => $payload,
			];
		}

		throw new Exceptions\InvalidArgument('Provided unsupported topic to parse');
	}

}
