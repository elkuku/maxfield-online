<?php

namespace App\Parser\Type;

use App\Parser\AbstractParser;
use JsonException;

class Json extends AbstractParser
{

    protected function getType(): string
    {
        return 'JsonRaw';
    }

    /**
     * @inheritDoc
     */
    public function parse(array $data): array
    {
        $waypoints = [];

        try {
            $items = json_decode(
                $data[$this->getType()],
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new \UnexpectedValueException(
                'Invalid multiexport JSON data'
            );
        }

        foreach ($items as $item) {
            $latlng = explode(',', $item['latlng']);

            if (2 !== count($latlng)) {
                throw new \UnexpectedValueException('Invalid latlng JSON data');
            }

            $waypoints[] = $this->createWayPoint(
                '',
                (float)$latlng[0],
                (float)$latlng[1],
                $item['title'],
            );
        }

        return $waypoints;
    }
}
