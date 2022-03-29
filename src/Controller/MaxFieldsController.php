<?php

namespace App\Controller;

use App\Entity\Maxfield;
use App\Form\MaxfieldType;
use App\Repository\WaypointRepository;
use App\Service\MaxFieldGenerator;
use App\Service\MaxFieldHelper;
use Doctrine\ORM\EntityManagerInterface;
use Elkuku\MaxfieldParser\JsonHelper;
use Elkuku\MaxfieldParser\MaxfieldParser;
use Exception;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: 'maxfield')]
class MaxFieldsController extends BaseController
{
    #[Route(path: '/generate', name: 'maxfield_generate')]
    public function generateMaxFields(
        WaypointRepository $repository,
        MaxFieldGenerator $maxFieldGenerator,
        MaxFieldHelper $maxFieldHelper,
        Request $request,
        EntityManagerInterface $entityManager,
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
            'skip_plots'      => true,
            'skip_step_plots' => true,
        ];

        $timeStamp = date('Y-m-d');
        $projectName = $playersNum.'pl-'.$timeStamp.'-'.$buildName;

        try {
            $maxFieldGenerator->generate(
                $projectName,
                $maxField,
                $playersNum,
                $options
            );

            $json = (new JsonHelper())
                ->getJsonData(
                    new MaxfieldParser(
                        $maxFieldGenerator->getPath($projectName)
                    )
                );

            $maxfield = (new Maxfield())
                ->setName($projectName)
                ->setJsonData($json)
                ->setOwner($this->getUser());

            $entityManager->persist($maxfield);
            $entityManager->flush();

            $this->addFlash('success', 'Maxfield has been created');
        } catch (IOExceptionInterface $exception) {
            echo 'An error occurred while creating your directory at '
                .$exception->getPath();
            $this->addFlash(
                'danger',
                'An error occurred while creating your directory at '
                .$exception->getPath()
            );
        } catch (Exception $exception) {
            $this->addFlash('danger', $exception->getMessage());
            echo $exception->getMessage();
        }

        return $this->redirectToRoute('default');
    }

    #[Route('/edit/{id}', name: 'maxfield_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        Maxfield $maxfield,
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')
            && $maxfield->getOwner() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException('No access for you!');
        }

        $form = $this->createForm(MaxfieldType::class, $maxfield);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var Maxfield $data
             */
            $data = $form->getData();
            $entityManager->persist($data);
            $entityManager->flush();

            $this->addFlash('success', 'Maxfield updated!');

            return $this->redirectToRoute('default');
        }

        return $this->renderForm(
            'maxfield/edit.html.twig',
            [
                'form'     => $form,
                'maxfield' => $maxfield,
            ]
        );
    }

    #[Route('/delete/{id}', name: 'maxfield_delete', methods: ['GET'])]
    public function delete(
        EntityManagerInterface $entityManager,
        Maxfield $maxfield,
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')
            && $maxfield->getOwner() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException('No access for you!');
        }

        $entityManager->remove($maxfield);
        $entityManager->flush();

        $this->addFlash('success', 'Maxfield has been removed.');

        return $this->redirectToRoute('default');
    }
}
