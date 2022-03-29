<?php

namespace App\Controller;

use App\Repository\WaypointRepository;
use App\Service\MaxFieldGenerator;
use App\Service\MaxFieldHelper;
use Elkuku\MaxfieldParser\GpxHelper;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: 'max-fields')]
class MaxFieldsController extends AbstractController
{
    #[Route(path: '/', name: 'max_fields')]
    public function index(MaxFieldHelper $maxFieldHelper): Response
    {
        return $this->render(
            'max_fields/index.html.twig',
            [
                'list'            => $maxFieldHelper->getList(),
                'maxfieldVersion' => $maxFieldHelper->getMaxfieldVersion(),
            ]
        );
    }

    #[Route(path: '/show/{item}', name: 'max_fields_result')]
    public function display(
        MaxFieldHelper $maxFieldHelper,
        string $item
    ): Response {
        return $this->render(
            'max_fields/result.html.twig',
            [
                'item'            => $item,
                'info'            => $maxFieldHelper->getMaxField($item),
                'maxfieldVersion' => $maxFieldHelper->getMaxfieldVersion(),
            ]
        );
    }

    #[Route(path: '/export', name: 'export-maxfields')]
    public function generateMaxFields(
        WaypointRepository $repository,
        MaxFieldGenerator $maxFieldGenerator,
        MaxFieldHelper $maxFieldHelper,
        Request $request
    ): Response {
        $points = $request->request->all('points');

        if (!$points) {
            throw new NotFoundHttpException('No waypoints selected.');
        }

        $wayPoints = $repository->findBy(['id' => $points]);
        $maxField = $maxFieldGenerator->convertWayPointsToMaxFields($wayPoints);

        $buildName = $request->request->get('buildName');
        $playersNum = (int)$request->request->get('players_num') ?: 1;
        $options = [
            'skip_plots'      => $request->request->getBoolean('skip_plots'),
            'skip_step_plots' => $request->request->getBoolean(
                'skip_step_plots'
            ),
        ];

        $timeStamp = date('Y-m-d');
        $projectName = $playersNum.'pl-'.$timeStamp.'-'.$buildName;

        $maxFieldGenerator->generate(
            $projectName,
            $maxField,
            $playersNum,
            $options
        );

        return $this->render(
            'max_fields/result.html.twig',
            [
                'item'            => $projectName,
                'info'            => $maxFieldHelper->getMaxField($projectName),
                'maxfieldVersion' => $maxFieldHelper->getMaxfieldVersion(),

            ]
        );
    }

    #[Route(path: '/send_mail', name: 'maxfields-send-mail')]
    public function sendMail(
        MaxFieldHelper $maxFieldHelper,
        MailerInterface $mailer,
        Request $request,
        Pdf $pdf
    ): JsonResponse {
        $agent = $request->get('agent');
        $email = $request->get('email');
        $item = $request->get('item');

        try {
            $info = $maxFieldHelper->getMaxField($item);

            $linkList = $pdf
                ->getOutputFromHtml(
                    $this->renderView(
                        'max_fields/link-list.html.twig',
                        [
                            'info'  => $info,
                            'agent' => $agent,
                        ]
                    ),
                    ['encoding' => 'utf-8']
                );

            $keyList = $pdf
                ->getOutputFromHtml(
                    $this->renderView(
                        'max_fields/pdf-keys.html.twig',
                        [
                            'info'  => $info,
                            'agent' => $agent,
                        ]
                    ),
                    ['encoding' => 'utf-8']
                );

            $email = (new TemplatedEmail())
                ->from($_ENV['MAILER_FROM_MAIL'])
                ->to($email)
                ->subject('MaxFields Plan '.$item)
                ->attach($linkList, 'link-list.pdf', 'application/pdf')
                ->attach($keyList, 'key-list.pdf', 'application/pdf')
                ->htmlTemplate('max_fields/email.html.twig')
                ->context(
                    [
                        'img_portal_map' => $item.'/portal_map.png',
                        'img_link_map'   => $item.'/link_map.png',
                        'item'           => $item,
                        'agent'          => $agent,
                        'info'           => $info,
                    ]
                );

            $mailer->send($email);
            $data = [
                'status'  => 'ok',
                'message' => 'Message has been sent.',
            ];
        } catch (\Exception $exception) {
            $data = [
                'status'  => 'error',
                'message' => 'error sending mail: '.$exception->getMessage(),
            ];
        }

        return $this->json($data);
    }

    #[Route(path: '/gpx/{item}', name: 'max_fields_gpx')]
    public function getGpx(string $item, GpxHelper $gpxHelper, MaxFieldHelper $maxFieldHelper): void
    {
        $gpx = $gpxHelper->getWaypointsGpx($maxFieldHelper->getParser($item));

        header('Content-type: text/plain');
        header(
            'Content-Disposition: attachment; filename="'.$item
            .'-waypoints.gpx"'
        );

        echo $gpx;

        exit();
    }

    #[Route(path: '/gpxroute/{item}', name: 'max_fields_gpxroute')]
    public function getGpxRoute(GpxHelper $gpxHelper, MaxFieldHelper $maxFieldHelper, string $item): void
    {
        $gpx = $gpxHelper->getRouteGpx($maxFieldHelper->getParser($item));

        header('Content-type: text/plain');
        header(
            'Content-Disposition: attachment; filename="'.$item.'-route.gpx"'
        );

        echo $gpx;

        exit();
    }

    #[Route(path: '/gpxtrack/{item}', name: 'max_fields_gpxtrack')]
    public function getGpxTrack(GpxHelper $gpxHelper, MaxFieldHelper $maxFieldHelper, string $item): void
    {
        $gpx = $gpxHelper->getTrackGpx($maxFieldHelper->getParser($item));

        header('Content-type: text/plain');
        header(
            'Content-Disposition: attachment; filename="'.$item.'-track.gpx"'
        );

        echo $gpx;

        exit();
    }

    #[Route(path: '/gpxroutetrack/{item}', name: 'max_fields_gpxroutetrack')]
    public function getGpxRouteTrack(GpxHelper $gpxHelper, MaxFieldHelper $maxFieldHelper, string $item): void
    {
        $gpx = $gpxHelper->getRouteTrackGpx($maxFieldHelper->getParser($item));

        header('Content-type: text/plain');
        header(
            'Content-Disposition: attachment; filename="'.$item.'-maxfield.gpx"'
        );

        echo $gpx;

        exit();
    }

    #[Route(path: '/delete/{item}', name: 'max_fields_delete')]
    public function delete(
        MaxFieldGenerator $maxFieldGenerator,
        MaxFieldHelper $maxFieldHelper,
        string $item
    ): Response {
        try {
            $maxFieldGenerator->remove($item);

            $this->addFlash('success', sprintf('%s has been removed.', $item));
        } catch (IOException $exception) {
            $this->addFlash('warning', $exception->getMessage());
        }

        return $this->render(
            'max_fields/index.html.twig',
            [
                'list'            => $maxFieldHelper->getList(),
                'maxfieldVersion' => $maxFieldHelper->getMaxfieldVersion(),
            ]
        );
    }

    #[Route(path: '/maxfield2strike', name: 'maxfields-maxfield2strike')]
    public function maxfield2strike(
        MaxField2Strike $maxField2Strike,
        Request $request
    ): JsonResponse {
        $opName = (string)$request->query->get('opName');
        $maxfieldName = (string)$request->query->get('maxfieldName');

        $result = $maxField2Strike->generateOp($opName, $maxfieldName);
        $data = [
            'status'  => 'ok',
            'message' => $result,
        ];

        return $this->json($data);
    }

    #[Route(path: '/log', name: 'maxfields-log')]
    public function getLog(StrikeLogger $logger): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/plain');
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($logger->getLog());

        return $response;
    }
}
