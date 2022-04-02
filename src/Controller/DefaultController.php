<?php

namespace App\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends BaseController
{
    #[Route('/', name: 'default', methods: ['GET'])]
    public function index(string $projectDir): Response {
        $fileSystem = new Filesystem();

        $projectRoot = $projectDir.'/public/maxfields/aaatest';

        if (is_dir($projectRoot)) {
            $this->addFlash('success', 'dir exists: '.$projectRoot);
        } else {
            $this->addFlash('danger', 'dir does not exists: '.$projectRoot);
            $fileSystem->mkdir($projectRoot);
        }

        if (is_dir($projectRoot)) {
            $this->addFlash('success', 'dir exists: '.$projectRoot);
        } else {
            throw new \UnexpectedValueException(
                'There was a problem creating your Project directory: '
                .$projectRoot
            );
        }

        $fileName = $projectRoot.'/portals.txt';
        $fileSystem->dumpFile($fileName, 'Hello Dolly :)');

        $hello = file_get_contents($fileName);

        return $this->render(
            'default/index.html.twig',
            [
                'maxfields' => $this->getUser()?->getMaxfields(),
                'hello' => $hello,
            ]
        );
    }
}
