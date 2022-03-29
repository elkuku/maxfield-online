<?php

namespace App\Parser\Type;

use App\Parser\AbstractParser;

class OffleJson extends AbstractParser
{

    protected function getType(): string
    {
        return 'OffleJson';
    }

    /**
     * @inheritDoc
     */
    public function parse(array $data): array
    {
        $waypoints = [];
        $jsonData = json_decode(
            $data[$this->getType()],
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!$jsonData) {
            throw new \UnexpectedValueException('Invalid JSON data received');
        }

        foreach ($jsonData as $item) {
            if (isset($item->name, $item->guid, $item->lat, $item->lng)) {
                $waypoints[] = $this->createWayPoint(
                    $item->guid,
                    $item->lat,
                    $item->lng,
                    $item->name,
                );
            }
        }

        return $waypoints;
    }
}
