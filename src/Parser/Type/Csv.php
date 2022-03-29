<?php

namespace App\Parser\Type;

use App\Parser\AbstractParser;

class Csv extends AbstractParser
{
    protected function getType(): string
    {
        return 'csvRaw';
    }

    public function parse(array $data): array
    {
        $lines = explode("\n", $data[$this->getType()]);
        $waypoints = [];

        foreach ($lines as $i => $line) {
            $line = trim($line);

            if (0 === $i || !$line) {
                continue;
            }

            $parts = explode(',', $line);

            if (4 !== \count($parts)) {
                $parts = $this->parseFishyCsvLine($parts);
                if (4 !== \count($parts)) {
                    throw new \UnexpectedValueException(
                        'Error parsing CSV file'
                    );
                }
            }

            $waypoints[] = $this->createWayPoint(
                '',
                (float)$parts[1],
                (float)$parts[2],
                $parts[0]
            );


            // Check image
            // $this->wayPointHelper->checkImage($wayPoint->getId(), trim($parts[3]));
        }

        return $waypoints;
    }

    /**
     * @param array<string> $parts
     *
     * @return array<string>
     */
    private function parseFishyCsvLine(array $parts): array
    {
        $returnValues = [];

        $cnt = \count($parts);

        $returnValues[3] = $parts[$cnt - 1];
        unset ($parts[$cnt - 1]);

        $returnValues[2] = $parts[$cnt - 2];
        unset ($parts[$cnt - 2]);

        $returnValues[1] = $parts[$cnt - 3];
        unset ($parts[$cnt - 3]);

        $returnValues[0] = implode('', $parts);

        return $returnValues;
    }
}
