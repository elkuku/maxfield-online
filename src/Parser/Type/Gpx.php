<?php

namespace App\Parser\Type;

use App\Parser\AbstractParser;
use Exception;

class Gpx extends AbstractParser
{

    protected function getType(): string
    {
        return 'gpxRaw';
    }

    public function parse(array $data): array
    {
        try {
            $xml = simplexml_load_string($data[$this->getType()]);
        } catch (Exception) {
            throw new \UnexpectedValueException('Invalid GPX data received!');
        }

        $waypoints = [];

        if (false === $xml) {
            throw new \UnexpectedValueException('Something wrong with your XML :(');
        }

        foreach ($xml->children() as $wp) {
            $waypoints[] = $this->createWayPoint(
                '',
                (float)$wp['lat'],
                (float)$wp['lon'],
                $wp->name
            );
        }

        return $waypoints;
    }
}
