<?php

namespace App\Parser\Type;

use App\Entity\Category;
use App\Entity\Waypoint;
use App\Parser\AbstractParser;

class IdmCsv extends AbstractParser
{

    protected function getType(): string
    {
        return 'idmcsvRaw';
    }

    /**
     * @inheritDoc
     */
    public function parse(array $data): array
    {
        $waypoints = [];

        foreach ($data as $line) {
            $line = trim($line);

            if (!$line) {
                continue;
            }

            $parts = explode(',', $line);

            if (3 !== \count($parts)) {
                throw new \UnexpectedValueException(
                    'Error parsing Idm CSV file'
                );
            }

            $waypoints[] = $this->createWayPoint(
                '',
                (float)$parts[1],
                (float)$parts[2],
                trim($parts[0], '"')
            );
        }

        return $waypoints;
    }
}
