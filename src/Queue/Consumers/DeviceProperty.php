<?php declare(strict_types = 1);

/**
 * DeviceProperty.php
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

use Doctrine\DBAL;
use FastyBird\Connector\Zigbee2Mqtt;
use FastyBird\Connector\Zigbee2Mqtt\Entities;
use FastyBird\Connector\Zigbee2Mqtt\Queries;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use FastyBird\Module\Devices\Entities as DevicesEntities;
use FastyBird\Module\Devices\Exceptions as DevicesExceptions;
use FastyBird\Module\Devices\Models as DevicesModels;
use FastyBird\Module\Devices\Queries as DevicesQueries;
use FastyBird\Module\Devices\Utilities as DevicesUtilities;
use Nette\Utils;
use Ramsey\Uuid;

/**
 * Device property consumer
 *
 * @package        FastyBird:Zigbee2MqttConnector!
 * @subpackage     Queue
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @property-read DevicesModels\Entities\Devices\DevicesRepository $devicesRepository
 * @property-read DevicesModels\Entities\Devices\Properties\PropertiesRepository $devicesPropertiesRepository
 * @property-read DevicesModels\Entities\Devices\Properties\PropertiesManager $devicesPropertiesManager
 * @property-read DevicesUtilities\Database $databaseHelper
 * @property-read Zigbee2Mqtt\Logger $logger
 */
trait DeviceProperty
{

	/**
	 * @param string|array<int, string>|array<int, string|int|float|array<int, string|int|float>|Utils\ArrayHash|null>|array<int, array<int, string|array<int, string|int|float|bool>|Utils\ArrayHash|null>>|null $format
	 *
	 * @throws DBAL\Exception
	 * @throws DevicesExceptions\InvalidState
	 * @throws DevicesExceptions\Runtime
	 */
	private function setDeviceProperty(
		Uuid\UuidInterface $deviceId,
		string|bool|int|null $value,
		MetadataTypes\DataType $dataType,
		string $identifier,
		string|null $name = null,
		array|string|null $format = null,
	): void
	{
		$findDevicePropertyQuery = new DevicesQueries\Entities\FindDeviceProperties();
		$findDevicePropertyQuery->byDeviceId($deviceId);
		$findDevicePropertyQuery->byIdentifier($identifier);

		$property = $this->devicesPropertiesRepository->findOneBy($findDevicePropertyQuery);

		if ($property !== null && $value === null) {
			$this->databaseHelper->transaction(
				function () use ($property): void {
					$this->devicesPropertiesManager->delete($property);
				},
			);

			return;
		}

		if ($value === null) {
			return;
		}

		if (
			$property !== null
			&& !$property instanceof DevicesEntities\Devices\Properties\Variable
		) {
			$findDevicePropertyQuery = new DevicesQueries\Entities\FindDeviceProperties();
			$findDevicePropertyQuery->byId($property->getId());

			$property = $this->devicesPropertiesRepository->findOneBy($findDevicePropertyQuery);

			if ($property !== null) {
				$this->databaseHelper->transaction(function () use ($property): void {
					$this->devicesPropertiesManager->delete($property);
				});

				$this->logger->warning(
					'Stored device property was not of valid type',
					[
						'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_ZIGBEE2MQTT,
						'type' => 'message-consumer',
						'device' => [
							'id' => $deviceId->toString(),
						],
						'property' => [
							'id' => $property->getId()->toString(),
							'identifier' => $identifier,
						],
					],
				);
			}

			$property = null;
		}

		if ($property === null) {
			$findDeviceQuery = new Queries\Entities\FindDevices();
			$findDeviceQuery->byId($deviceId);

			$device = $this->devicesRepository->findOneBy(
				$findDeviceQuery,
				Entities\Zigbee2MqttDevice::class,
			);

			if ($device === null) {
				$this->logger->error(
					'Device was not found, property could not be configured',
					[
						'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_ZIGBEE2MQTT,
						'type' => 'message-consumer',
						'device' => [
							'id' => $deviceId->toString(),
						],
						'property' => [
							'identifier' => $identifier,
						],
					],
				);

				return;
			}

			$property = $this->databaseHelper->transaction(
				fn (): DevicesEntities\Devices\Properties\Property => $this->devicesPropertiesManager->create(
					Utils\ArrayHash::from([
						'entity' => DevicesEntities\Devices\Properties\Variable::class,
						'device' => $device,
						'identifier' => $identifier,
						'name' => $name,
						'dataType' => $dataType,
						'value' => $value,
						'format' => $format,
					]),
				),
			);

			$this->logger->debug(
				'Device variable property was created',
				[
					'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_ZIGBEE2MQTT,
					'type' => 'message-consumer',
					'device' => [
						'id' => $deviceId->toString(),
					],
					'property' => [
						'id' => $property->getId()->toString(),
						'identifier' => $identifier,
					],
				],
			);

		} else {
			$property = $this->databaseHelper->transaction(
				fn (): DevicesEntities\Devices\Properties\Property => $this->devicesPropertiesManager->update(
					$property,
					Utils\ArrayHash::from([
						'dataType' => $dataType,
						'value' => $value,
						'format' => $format,
					]),
				),
			);

			$this->logger->debug(
				'Device variable property was updated',
				[
					'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_ZIGBEE2MQTT,
					'type' => 'message-consumer',
					'device' => [
						'id' => $deviceId->toString(),
					],
					'property' => [
						'id' => $property->getId()->toString(),
						'identifier' => $identifier,
					],
				],
			);
		}
	}

}
