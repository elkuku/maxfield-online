<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 11.10.18
 * Time: 10:32
 */

namespace App\Service;

use App\Entity\Waypoint;
use DirectoryIterator;
use Exception;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This is for https://github.com/tvwenger/maxfield
 */
class MaxFieldGenerator
{
    /**
     * @var string
     */
    protected string $rootDir = '';

    /**
     * @var int
     */
    private int $maxfieldVersion;

    /**
     * @var string
     */
    private string $maxfieldExec;

    /**
     * @var string
     */
    private string $googleApiKey;

    /**
     * @var string
     */
    private string $googleApiSecret;

    public function __construct(
        string $projectDir,
        string $maxfieldExec,
        int $maxfieldVersion,
        string $googleApiKey,
        string $googleApiSecret,
    ) {
        $this->rootDir = $projectDir.'/public/maxfields';

        // Path to makePlan.py
        $this->maxfieldExec = $maxfieldExec;
        $this->maxfieldVersion = $maxfieldVersion;
        $this->googleApiKey = $googleApiKey;
        $this->googleApiSecret = $googleApiSecret;
    }

    /**
     * @param array<string, bool> $options
     */
    public function generate(
        string $projectName,
        string $wayPointList,
        int $playersNum,
        array $options
    ): void {
        $fileSystem = new Filesystem();

        $projectRoot = $this->rootDir.'/'.$projectName;

        if (is_dir($projectRoot)) {
            throw new \UnexpectedValueException(
                'Project directory already exists!'
            );
        }

        $fileSystem->mkdir($projectRoot);

        if (!is_dir($projectRoot)) {
            throw new \UnexpectedValueException(
                'There was a problem creating your Project directory: '
                .$projectRoot
            );
        }

        $fileName = $projectRoot.'/portals.txt';
        $fileSystem->appendToFile($fileName, $wayPointList);

        if ($this->maxfieldVersion < 4) {
            $command = "python {$this->maxfieldExec} $fileName"
                ." -d $projectRoot -f output.pkl -n $playersNum";
        } else {
            $command = "{$this->maxfieldExec} $fileName"
                ." --outdir $projectRoot --num_agents $playersNum --output_csv"
                ." --num_cpus 0 --num_field_iterations 100 --max_route_solutions 100";

            if ($this->googleApiKey) {
                $command .= ' --google_api_key '.$this->googleApiKey;
                $command .= ' --google_api_secret '.$this->googleApiSecret;
            }

            if ($options['skip_plots']) {
                $command .= " --skip_plots";
            }

            if ($options['skip_step_plots']) {
                $command .= " --skip_step_plots";
            }

            $command .= " --verbose 2>&1 > $projectRoot/log.txt 2>&1";
        }

        $fileSystem->dumpFile($projectRoot.'/command.txt', $command);

        $output = [];
        $resultCode = 0;
        $returnVal = exec($command, $output, $resultCode);

        if (false === $returnVal) {
            throw new \UnexpectedValueException(
                sprintf(
                    'Command returned a failure: code: %s - %s',
                    $resultCode,
                    print_r($output, true)
                )
            );
        }
    }

    /**
     * @return array<string>
     */
    public function getContentList(string $item): array
    {
        $list = [];

        foreach (new DirectoryIterator($this->rootDir.'/'.$item) as $fileInfo) {
            if ($fileInfo->isFile()) {
                $list[] = $fileInfo->getFilename();
            }
        }

        sort($list);

        return $list;
    }

    /**
     * @param Waypoint[] $wayPoints
     */
    public function convertWayPointsToMaxFields(array $wayPoints): string
    {
        $maxFields = [];

        foreach ($wayPoints as $wayPoint) {
            $points = $wayPoint->getLat().','.$wayPoint->getLon();
            $name = str_replace([';', '#'], '', (string)$wayPoint->getName());
            $maxFields[] = $name.'; '.$_ENV['INTEL_URL']
                .'?ll='.$points.'&z=1&pll='.$points;
        }

        return implode("\n", $maxFields);
    }

    public function getImagePath(string $item, string $image): string
    {
        return $this->rootDir."/$item/$image";
    }

    public function remove(string $item): void
    {
        $fileSystem = new Filesystem();

        $fileSystem->remove($this->rootDir."/$item");
    }

    public function getPath(string $projectDir = ''): string
    {
        return $projectDir ? $this->rootDir."/$projectDir" : $this->rootDir;
    }

    private function findFrames(string $item): int
    {
        $path = $this->rootDir.'/'.$item.'/frames';
        $frames = 0;

        if (false === file_exists($path)) {
            return $frames;
        }

        foreach (new \DirectoryIterator($path) as $file) {
            if (preg_match(
                '/frame_(\d\d\d\d\d)/',
                $file->getFilename(),
                $matches
            )
            ) {
                $x = (int)$matches[1];
                $frames = max($x, $frames);
            }
        }

        return $frames;
    }
}
