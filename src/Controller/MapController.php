<?php

namespace App\Controller;

use App\Entity\Maxfield;
use App\Entity\User;
use App\Repository\WaypointRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/map')]
#[IsGranted(User::ROLES['agent'])]
class MapController extends AbstractController
{
    #[Route(path: '/create', name: 'map_create', methods: ['GET'])]
    public function create(): Response
    {
        return $this->render('map/create.html.twig');
    }

    #[Route(path: '/play/{id}', name: 'map_play', methods: ['GET'])]
    public function show(Maxfield $maxfield): Response
    {
        return $this->render(
            'map/play.html.twig',
            [
                'maxfield' => $maxfield,
            ]
        );
    }

    #[Route(path: '/get-waypoints', name: 'map_get_waypoints')]
    public function getWaypoints(WaypointRepository $repository): JsonResponse
    {
        $waypoints = $repository->findAll();

        $wps = [];

        foreach ($waypoints as $waypoint) {
            $w = [];

            $w['name'] = $waypoint->getName();
            $w['lat'] = $waypoint->getLat();
            $w['lng'] = $waypoint->getLon();
            $w['id'] = $waypoint->getId();

            $wps[] = $w;
        }

        return $this->json($wps);
    }
}
