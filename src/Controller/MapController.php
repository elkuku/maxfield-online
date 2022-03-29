<?php

namespace App\Controller;

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
    #[Route(path: '/maxfield', name: 'map-maxfield')]
    public function map(): Response
    {
        return $this->render('maps/maxfield.html.twig');
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
