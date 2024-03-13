<?php


namespace Elenyum\OpenAPI\Service\PropertyDescriber;

interface PropertyDescriberAwareInterface
{
    public function setPropertyDescriber(PropertyDescriberInterface $propertyDescriber): void;
}
