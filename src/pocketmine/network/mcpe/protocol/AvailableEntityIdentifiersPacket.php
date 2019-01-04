<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\network\mcpe\handler\SessionHandler;
use function base64_decode;

class AvailableEntityIdentifiersPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::AVAILABLE_ENTITY_IDENTIFIERS_PACKET;

	/**
	 * Hardcoded NBT blob extracted from MCPE vanilla server.
	 * TODO: this needs to be generated dynamically, but this is here for stable backwards compatibility, so we don't care for now.
	 */
	private const HARDCODED_NBT_BLOB = "CgAJBmlkbGlzdArEAQgDYmlkCm1pbmVjcmFmdDoBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAAgCaWQQbWluZWNyYWZ0OnBsYXllcgMDcmlkhgQBCnN1bW1vbmFibGUAAAgDYmlkCm1pbmVjcmFmdDoBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAAgCaWQXbWluZWNyYWZ0OnRyaXBvZF9jYW1lcmEDA3JpZIQEAQpzdW1tb25hYmxlAAAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwEIAmlkGW1pbmVjcmFmdDp3aXRoZXJfc2tlbGV0b24DA3JpZGABCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQObWluZWNyYWZ0Omh1c2sDA3JpZF4BCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQPbWluZWNyYWZ0OnN0cmF5AwNyaWRcAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwEIAmlkD21pbmVjcmFmdDp3aXRjaAMDcmlkWgEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZBltaW5lY3JhZnQ6em9tYmllX3ZpbGxhZ2VyAwNyaWRYAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwEIAmlkD21pbmVjcmFmdDpibGF6ZQMDcmlkVgEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZBRtaW5lY3JhZnQ6bWFnbWFfY3ViZQMDcmlkVAEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZA9taW5lY3JhZnQ6Z2hhc3QDA3JpZFIBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQVbWluZWNyYWZ0OmNhdmVfc3BpZGVyAwNyaWRQAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwEIAmlkFG1pbmVjcmFmdDpzaWx2ZXJmaXNoAwNyaWROAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwEIAmlkEm1pbmVjcmFmdDplbmRlcm1hbgMDcmlkTAEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZA9taW5lY3JhZnQ6c2xpbWUDA3JpZEoBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQXbWluZWNyYWZ0OnpvbWJpZV9waWdtYW4DA3JpZEgBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQQbWluZWNyYWZ0OnNwaWRlcgMDcmlkRgEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZBJtaW5lY3JhZnQ6c2tlbGV0b24DA3JpZEQBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQRbWluZWNyYWZ0OmNyZWVwZXIDA3JpZEIBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQQbWluZWNyYWZ0OnpvbWJpZQMDcmlkQAEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZBhtaW5lY3JhZnQ6c2tlbGV0b25faG9yc2UDA3JpZDQBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQObWluZWNyYWZ0Om11bGUDA3JpZDIBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQQbWluZWNyYWZ0OmRvbmtleQMDcmlkMAEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZBFtaW5lY3JhZnQ6ZG9scGhpbgMDcmlkPgEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZBZtaW5lY3JhZnQ6dHJvcGljYWxmaXNoAwNyaWTeAQEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZA5taW5lY3JhZnQ6d29sZgMDcmlkHAEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZA9taW5lY3JhZnQ6c3F1aWQDA3JpZCIBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQRbWluZWNyYWZ0OmRyb3duZWQDA3JpZNwBAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwEIAmlkD21pbmVjcmFmdDpzaGVlcAMDcmlkGgEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZBNtaW5lY3JhZnQ6bW9vc2hyb29tAwNyaWQgAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwEIAmlkD21pbmVjcmFmdDpwYW5kYQMDcmlk4gEBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQQbWluZWNyYWZ0OnNhbG1vbgMDcmlk2gEBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQNbWluZWNyYWZ0OnBpZwMDcmlkGAEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZBJtaW5lY3JhZnQ6dmlsbGFnZXIDA3JpZB4BCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQNbWluZWNyYWZ0OmNvZAMDcmlk4AEBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQUbWluZWNyYWZ0OnB1ZmZlcmZpc2gDA3JpZNgBAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwEIAmlkDW1pbmVjcmFmdDpjb3cDA3JpZBYBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQRbWluZWNyYWZ0OmNoaWNrZW4DA3JpZBQBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAAgCaWQRbWluZWNyYWZ0OmJhbGxvb24DA3JpZNYBAQpzdW1tb25hYmxlAAAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwEIAmlkD21pbmVjcmFmdDpsbGFtYQMDcmlkOgEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZBRtaW5lY3JhZnQ6aXJvbl9nb2xlbQMDcmlkKAEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZBBtaW5lY3JhZnQ6cmFiYml0AwNyaWQkAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwAIAmlkFG1pbmVjcmFmdDpzbm93X2dvbGVtAwNyaWQqAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwEIAmlkDW1pbmVjcmFmdDpiYXQDA3JpZCYBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQQbWluZWNyYWZ0Om9jZWxvdAMDcmlkLAEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZA9taW5lY3JhZnQ6aG9yc2UDA3JpZC4BCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQNbWluZWNyYWZ0OmNhdAMDcmlklgEBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQUbWluZWNyYWZ0OnBvbGFyX2JlYXIDA3JpZDgBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQWbWluZWNyYWZ0OnpvbWJpZV9ob3JzZQMDcmlkNgEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZBBtaW5lY3JhZnQ6dHVydGxlAwNyaWSUAQEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZBBtaW5lY3JhZnQ6cGFycm90AwNyaWQ8AQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwEIAmlkEm1pbmVjcmFmdDpndWFyZGlhbgMDcmlkYgEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZBhtaW5lY3JhZnQ6ZWxkZXJfZ3VhcmRpYW4DA3JpZGQBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQUbWluZWNyYWZ0OnZpbmRpY2F0b3IDA3JpZHIBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAAgCaWQQbWluZWNyYWZ0OndpdGhlcgMDcmlkaAEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZBZtaW5lY3JhZnQ6ZW5kZXJfZHJhZ29uAwNyaWRqAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwEIAmlkEW1pbmVjcmFmdDpzaHVsa2VyAwNyaWRsAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwEIAmlkE21pbmVjcmFmdDplbmRlcm1pdGUDA3JpZG4BCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAAgCaWQSbWluZWNyYWZ0Om1pbmVjYXJ0AwNyaWSoAQEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZBltaW5lY3JhZnQ6aG9wcGVyX21pbmVjYXJ0AwNyaWTAAQEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZBZtaW5lY3JhZnQ6dG50X21pbmVjYXJ0AwNyaWTCAQEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZBhtaW5lY3JhZnQ6Y2hlc3RfbWluZWNhcnQDA3JpZMQBAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwAIAmlkIG1pbmVjcmFmdDpjb21tYW5kX2Jsb2NrX21pbmVjYXJ0AwNyaWTIAQEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZBVtaW5lY3JhZnQ6YXJtb3Jfc3RhbmQDA3JpZHoBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAAgCaWQObWluZWNyYWZ0Oml0ZW0DA3JpZIABAQpzdW1tb25hYmxlAAAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwAIAmlkDW1pbmVjcmFmdDp0bnQDA3JpZIIBAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwAIAmlkF21pbmVjcmFmdDpmYWxsaW5nX2Jsb2NrAwNyaWSEAQEKc3VtbW9uYWJsZQAACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZBNtaW5lY3JhZnQ6eHBfYm90dGxlAwNyaWSIAQEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZBBtaW5lY3JhZnQ6eHBfb3JiAwNyaWSKAQEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZB1taW5lY3JhZnQ6ZXllX29mX2VuZGVyX3NpZ25hbAMDcmlkjAEBCnN1bW1vbmFibGUAAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAAgCaWQXbWluZWNyYWZ0OmVuZGVyX2NyeXN0YWwDA3JpZI4BAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwAIAmlkGG1pbmVjcmFmdDpzaHVsa2VyX2J1bGxldAMDcmlkmAEBCnN1bW1vbmFibGUAAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAAgCaWQWbWluZWNyYWZ0OmZpc2hpbmdfaG9vawMDcmlkmgEBCnN1bW1vbmFibGUAAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAAgCaWQZbWluZWNyYWZ0OmRyYWdvbl9maXJlYmFsbAMDcmlkngEBCnN1bW1vbmFibGUAAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAAgCaWQPbWluZWNyYWZ0OmFycm93AwNyaWSgAQEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZBJtaW5lY3JhZnQ6c25vd2JhbGwDA3JpZKIBAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwAIAmlkDW1pbmVjcmFmdDplZ2cDA3JpZKQBAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwAIAmlkEm1pbmVjcmFmdDpwYWludGluZwMDcmlkpgEBCnN1bW1vbmFibGUAAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAAgCaWQYbWluZWNyYWZ0OnRocm93bl90cmlkZW50AwNyaWSSAQEKc3VtbW9uYWJsZQAACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZBJtaW5lY3JhZnQ6ZmlyZWJhbGwDA3JpZKoBAQpzdW1tb25hYmxlAAAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwAIAmlkF21pbmVjcmFmdDpzcGxhc2hfcG90aW9uAwNyaWSsAQEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZBVtaW5lY3JhZnQ6ZW5kZXJfcGVhcmwDA3JpZK4BAQpzdW1tb25hYmxlAAAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwAIAmlkFG1pbmVjcmFmdDpsZWFzaF9rbm90AwNyaWSwAQEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZBZtaW5lY3JhZnQ6d2l0aGVyX3NrdWxsAwNyaWSyAQEKc3VtbW9uYWJsZQAACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZCBtaW5lY3JhZnQ6d2l0aGVyX3NrdWxsX2Rhbmdlcm91cwMDcmlktgEBCnN1bW1vbmFibGUAAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAAgCaWQObWluZWNyYWZ0OmJvYXQDA3JpZLQBAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwAIAmlkGG1pbmVjcmFmdDpsaWdodG5pbmdfYm9sdAMDcmlkugEBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAAgCaWQYbWluZWNyYWZ0OnNtYWxsX2ZpcmViYWxsAwNyaWS8AQEKc3VtbW9uYWJsZQAACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZBRtaW5lY3JhZnQ6bGxhbWFfc3BpdAMDcmlkzAEBCnN1bW1vbmFibGUAAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAAgCaWQbbWluZWNyYWZ0OmFyZWFfZWZmZWN0X2Nsb3VkAwNyaWS+AQEKc3VtbW9uYWJsZQAACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZBptaW5lY3JhZnQ6bGluZ2VyaW5nX3BvdGlvbgMDcmlkygEBCnN1bW1vbmFibGUAAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAAgCaWQabWluZWNyYWZ0OmZpcmV3b3Jrc19yb2NrZXQDA3JpZJABAQpzdW1tb25hYmxlAQAIA2JpZAE6AQxleHBlcmltZW50YWwAAQtoYXNzcGF3bmVnZwAIAmlkGG1pbmVjcmFmdDpldm9jYXRpb25fZmFuZwMDcmlkzgEBCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAQgCaWQbbWluZWNyYWZ0OmV2b2NhdGlvbl9pbGxhZ2VyAwNyaWTQAQEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZA1taW5lY3JhZnQ6dmV4AwNyaWTSAQEKc3VtbW9uYWJsZQEACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZA9taW5lY3JhZnQ6YWdlbnQDA3JpZHABCnN1bW1vbmFibGUBAAgDYmlkAToBDGV4cGVyaW1lbnRhbAABC2hhc3NwYXduZWdnAAgCaWQSbWluZWNyYWZ0OmljZV9ib21iAwNyaWTUAQEKc3VtbW9uYWJsZQAACANiaWQBOgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cBCAJpZBFtaW5lY3JhZnQ6cGhhbnRvbQMDcmlkdAEKc3VtbW9uYWJsZQEACANiaWQKbWluZWNyYWZ0OgEMZXhwZXJpbWVudGFsAAELaGFzc3Bhd25lZ2cACAJpZA1taW5lY3JhZnQ6bnBjAwNyaWSCBAEKc3VtbW9uYWJsZQEAAA==";

	/** @var string */
	public $namedtag;

	protected function decodePayload() : void{
		$this->namedtag = $this->getRemaining();
	}

	protected function encodePayload() : void{
		$this->put($this->namedtag ?? base64_decode(self::HARDCODED_NBT_BLOB));
	}

	public function handle(SessionHandler $handler) : bool{
		return $handler->handleAvailableEntityIdentifiers($this);
	}
}
