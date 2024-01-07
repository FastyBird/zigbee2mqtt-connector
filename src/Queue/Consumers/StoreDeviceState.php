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
 * @date           01.01.24
 */

namespace FastyBird\Connector\Zigbee2Mqtt\Queue\Consumers;

use FastyBird\Connector\Zigbee2Mqtt;
use FastyBird\Connector\Zigbee2Mqtt\Entities;
use FastyBird\Connector\Zigbee2Mqtt\Queue;
use FastyBird\Library\Metadata\Documents as MetadataDocuments;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use FastyBird\Library\Metadata\Utilities as MetadataUtilities;
use FastyBird\Module\Devices\Exceptions as DevicesExceptions;
use FastyBird\Module\Devices\Models as DevicesModels;
use FastyBird\Module\Devices\Queries as DevicesQueries;
use FastyBird\Module\Devices\States as DevicesStates;
use FastyBird\Module\Devices\Utilities as DevicesUtilities;
use Nette;
use Nette\Utils;
use function array_merge;
use function implode;
use function preg_match;
use function sprintf;

/**
 * Store device state message consumer
 *
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Queue
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class StoreDeviceState implements Queue\Consumer
{

	use Nette\SmartObject;

	public function __construct(
		private readonly Zigbee2Mqtt\Logger $logger,
		private readonly DevicesModels\Configuration\Devices\Repository $devicesConfigurationRepository,
		private readonly DevicesModels\Configuration\Devices\Properties\Repository $devicesPropertiesConfigurationRepository,
		private readonly DevicesModels\Configuration\Channels\Repository $channelsConfigurationRepository,
		private readonly DevicesModels\Configuration\Channels\Properties\Repository $channelsPropertiesConfigurationRepository,
		private readonly DevicesUtilities\ChannelPropertiesStates $channelPropertiesStatesManager,
	)
	{
	}

	/**
	 * @throws DevicesExceptions\InvalidArgument
	 * @throws DevicesExceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 */
	public function consume(Entities\Messages\Entity $entity): bool
	{
		if (!$entity instanceof Entities\Messages\StoreDeviceState) {
			return false;
		}

		$findDevicePropertyQuery = new DevicesQueries\Configuration\FindDeviceVariableProperties();
		$findDevicePropertyQuery->byIdentifier(Zigbee2Mqtt\Types\DevicePropertyIdentifier::BASE_TOPIC);
		$findDevicePropertyQuery->byValue($entity->getBaseTopic());

		$baseTopicProperty = $this->devicesPropertiesConfigurationRepository->findOneBy(
			$findDevicePropertyQuery,
			MetadataDocuments\DevicesModule\DeviceVariableProperty::class,
		);

		if ($baseTopicProperty === null) {
			return true;
		}

		$findDeviceQuery = new DevicesQueries\Configuration\FindDevices();
		$findDeviceQuery->byConnectorId($entity->getConnector());
		$findDeviceQuery->byId($baseTopicProperty->getDevice());
		$findDeviceQuery->byType(Entities\Devices\Bridge::TYPE);

		$bridge = $this->devicesConfigurationRepository->findOneBy($findDeviceQuery);

		if ($bridge === null) {
			return true;
		}

		$findDevicePropertyQuery = new DevicesQueries\Configuration\FindDeviceVariableProperties();
		$findDevicePropertyQuery->byValue($entity->getDevice());

		if (preg_match(Zigbee2Mqtt\Constants::IEEE_ADDRESS_REGEX, $entity->getDevice()) === 1) {
			$findDevicePropertyQuery->byIdentifier(Zigbee2Mqtt\Types\DevicePropertyIdentifier::IEEE_ADDRESS);

		} else {
			$findDevicePropertyQuery->byIdentifier(Zigbee2Mqtt\Types\DevicePropertyIdentifier::FRIENDLY_NAME);
		}

		$deviceTypeProperty = $this->devicesPropertiesConfigurationRepository->findOneBy(
			$findDevicePropertyQuery,
			MetadataDocuments\DevicesModule\DeviceVariableProperty::class,
		);

		if ($deviceTypeProperty === null) {
			return true;
		}

		$findDeviceQuery = new DevicesQueries\Configuration\FindDevices();
		$findDeviceQuery->byConnectorId($entity->getConnector());
		$findDeviceQuery->byId($deviceTypeProperty->getDevice());
		$findDeviceQuery->forParent($bridge);
		$findDeviceQuery->byType(Entities\Devices\SubDevice::TYPE);

		$device = $this->devicesConfigurationRepository->findOneBy($findDeviceQuery);

		if ($device === null) {
			return true;
		}

		$this->processStates($device, $entity->getStates());

		$this->logger->debug(
			'Consumed device state message',
			[
				'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_ZIGBEE2MQTT,
				'type' => 'store-device-state-message-consumer',
				'connector' => [
					'id' => $entity->getConnector()->toString(),
				],
				'data' => $entity->toArray(),
			],
		);

		return true;
	}

	/**
	 * @param array<Entities\Messages\SingleExposeData|Entities\Messages\CompositeExposeData> $states
	 * @param array<string> $identifiers
	 *
	 * @throws DevicesExceptions\InvalidArgument
	 * @throws DevicesExceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 */
	private function processStates(
		MetadataDocuments\DevicesModule\Device $device,
		array $states,
		array $identifiers = [],
	): void
	{
		foreach ($states as $state) {
			if ($state instanceof Entities\Messages\SingleExposeData) {
				$findChannelQuery = new DevicesQueries\Configuration\FindChannels();
				$findChannelQuery->forDevice($device);
				$findChannelQuery->endWithIdentifier(
					sprintf(
						'_%s',
						implode('_', array_merge($identifiers, [$state->getIdentifier()])),
					),
				);
				$findChannelQuery->byType(Entities\Zigbee2MqttChannel::TYPE);

				$channel = $this->channelsConfigurationRepository->findOneBy($findChannelQuery);

				if ($channel === null) {
					$this->logger->warning(
						'Channel for storing device state could not be loaded',
						[
							'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_ZIGBEE2MQTT,
							'type' => 'store-device-state-message-consumer',
							'connector' => [
								'id' => $device->getConnector()->toString(),
							],
							'device' => [
								'id' => $device->getId()->toString(),
							],
							'data' => $state->toArray(),
						],
					);

					continue;
				}

				$findChannelPropertyQuery = new DevicesQueries\Configuration\FindChannelDynamicProperties();
				$findChannelPropertyQuery->forChannel($channel);
				$findChannelPropertyQuery->byIdentifier($state->getIdentifier());

				$property = $this->channelsPropertiesConfigurationRepository->findOneBy(
					$findChannelPropertyQuery,
					MetadataDocuments\DevicesModule\ChannelDynamicProperty::class,
				);

				if ($property === null) {
					$this->logger->warning(
						'Channel property for storing device state could not be loaded',
						[
							'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_ZIGBEE2MQTT,
							'type' => 'store-device-state-message-consumer',
							'connector' => [
								'id' => $device->getConnector()->toString(),
							],
							'device' => [
								'id' => $device->getId()->toString(),
							],
							'data' => $state->toArray(),
						],
					);

					continue;
				}

				$this->channelPropertiesStatesManager->writeValue(
					$property,
					Utils\ArrayHash::from([
						DevicesStates\Property::ACTUAL_VALUE_FIELD => MetadataUtilities\ValueHelper::transformValueFromDevice(
							$property->getDataType(),
							$property->getFormat(),
							$state->getValue(),
						),
						DevicesStates\Property::VALID_FIELD => true,
					]),
				);

			} else {
				$this->processStates(
					$device,
					$state->getStates(),
					array_merge($identifiers, [$state->getIdentifier()]),
				);
			}
		}
	}

}
