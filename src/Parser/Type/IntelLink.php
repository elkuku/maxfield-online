<?php

namespace App\Parser\Type;

use App\Parser\AbstractParser;
use UnexpectedValueException;
use function count;
use function in_array;

class IntelLink extends AbstractParser
{
    protected function getType(): string
    {
        return 'intelLink';
    }

    public function parse(array $data): array
    {
        $intelLink = $data[$this->getType()];

        $parts = explode('pls=', $intelLink);

        if (false === isset($parts[1])) {
            throw new UnexpectedValueException('Invalid intel link');
        }

        $pairs = explode('_', $parts[1]);

        $wayPoints = [];
        $ws = [];

        foreach ($pairs as $pair) {
            $points = explode(',', $pair);
            if (4 === count($points)) {
                // First pair
                $p = $points[0].','.$points[1];
                if (false === in_array($p, $ws, false)) {
                    $wayPoints[] = $this->createWayPoint(
                        'x',
                        (float)$points[0],
                        (float)$points[1],
                        (string)(count($ws) + 1)
                    );

                    $ws[] = $p;
                }

                // Second pair
                $p = $points[2].','.$points[3];
                if (false === in_array($p, $ws, false)) {
                    $wayPoints[] = $this->createWayPoint(
                        'x',
                        (float)$points[2],
                        (float)$points[3],
                        (string)(count($ws) + 1)
                    );

                    $ws[] = $p;
                }
            }
        }

        return $wayPoints;
    }
}
