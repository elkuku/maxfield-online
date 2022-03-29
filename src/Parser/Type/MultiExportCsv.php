<?php

namespace App\Parser\Type;

use App\Entity\Category;
use App\Entity\Waypoint;
use App\Parser\AbstractParser;

class MultiExportCsv extends AbstractParser
{
    protected function getType(): string
    {
        return 'multiexportcsv';
    }

    /**
     * @inheritDoc
     */
    public function parse(array $data): array
    {
        $waypoints = [];

        foreach ($data as $line) {
            $line = trim($line);

            $parts = explode(',', $line);

            if (3 !== \count($parts)) {
                $parts = $this->parseFishyCsvLine2($line);
                if (3 !== \count($parts)) {
                    throw new \UnexpectedValueException(
                        'Error parsing CSV file'
                    );
                }
            }

            $lat = (float)$parts[1];
            $lon = (float)$parts[2];

            $wayPoint = new Waypoint();

            $wayPoint->setName(trim((string)$parts[0], '"'));
            $wayPoint->setLat($lat);
            $wayPoint->setLon($lon);

            $waypoints[] = $wayPoint;
        }

        return $waypoints;
    }

    /**
     * @return array<int, string|null>
     */
    private function parseFishyCsvLine2(string $line): array
    {
        $parts = explode(',', $line);

        $newParts = [];

        $newParts[2] = array_pop($parts);
        $newParts[1] = array_pop($parts);
        $newParts[0] = implode(',', $parts);

        return $newParts;
    }
}
