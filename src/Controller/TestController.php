<?php

namespace App\Controller;

use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted(User::ROLES['admin'])]
#[Route('/test')]
class TestController extends AbstractController
{
    private string $rootDir;

    public function __construct(string $projectDir)
    {
        $this->rootDir = $projectDir.'/public/maxfields';
    }

    #[Route('/', name: 'app_test')]
    public function index(): Response
    {
        return $this->render('test/index.html.twig', [
            'directories' => $this->findDirectories(),
        ]);
    }

    #[Route('/log/{name}', name: 'app_test_log')]
    public function showLog(string $name): Response
    {
        $fileName = $this->rootDir."/$name/log.txt";
        $contents = file_exists($fileName) ? file_get_contents($fileName)
            : 'No Logfile!:(';

        return $this->render('test/index.html.twig', [
            'directories' => $this->findDirectories(),
            'dirName'     => $name,
            'contents'    => $contents,
        ]);
    }

    private function findDirectories(): Finder
    {
        $finder = new Finder();

        return $finder->directories()->in($this->rootDir);
    }
}
