<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends BaseController
{
    #[Route('/', name: 'default', methods: ['GET'])]
    public function index(string $projectDir): Response {
        return $this->render(
            'default/index.html.twig',
            [
                'maxfields' => $this->getUser()?->getMaxfields(),
                'controller_name' => 'DefaultController',
                'php_version'     => PHP_VERSION,
                'symfony_version' => Kernel::VERSION,
                'project_dir'     => $projectDir,
            ]
        );
    }
}
