<?php

/**
 *
 * @author Josef Nevoral <josef.nevoral@hotelquickly.com>
 *
 */

namespace HQ;

interface Exception
{

}

class InvalidStateException extends \RuntimeException implements Exception
{

}

class InvalidArgumentException extends \RuntimeException implements Exception
{

}

class IOException extends \RuntimeException implements Exception
{

}

class NotSupportedException extends \LogicException implements Exception
{

}

class DatabaseException extends \Exception implements Exception
{

}

class UnauthorizedAccessException extends \Exception implements Exception {

}

class InvalidEmailException extends \Exception implements Exception {

}