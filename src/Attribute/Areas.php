<?php

namespace Elenyum\OpenAPI\Attribute;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class Areas
{
    /** @var string[] */
    private $areas;

    public function __construct(array $properties)
    {
        if (!array_key_exists('value', $properties) || !is_array($properties['value'])) {
            $properties['value'] = array_values($properties);
        }

        if ([] === $properties['value']) {
            throw new \InvalidArgumentException('An array of areas was expected');
        }

        $areas = [];
        foreach ($properties['value'] as $area) {
            if (!is_string($area)) {
                throw new \InvalidArgumentException('An area must be given as a string');
            }

            if (!in_array($area, $areas)) {
                $areas[] = $area;
            }
        }

        if (0 === count($areas)) {
            throw new \LogicException('At least one area is expected');
        }

        $this->areas = $areas;
    }

    public function has(string $area): bool
    {
        return in_array($area, $this->areas, true);
    }
}
