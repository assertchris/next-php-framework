<?php

namespace Tests;

function valueOf(object $object, string $property)
{
    $reflection = new \ReflectionObject($object);

    $property = $reflection->getProperty($property);
    $property->setAccessible(true);

    return $property->getValue($object);
}
