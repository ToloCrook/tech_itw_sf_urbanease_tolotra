<?php

namespace App\Controller;

use App\Entity\Boat;
use App\Form\BoatType;
use App\Repository\BoatRepository;
use App\Service\MapManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/boat")
 */
class BoatController extends AbstractController
{

    /**
     * Move the boat to coord x,y
     * @Route("/move/{x}/{y}", name="moveBoat", requirements={"x"="\d+", "y"="\d+"}))
     */
    public function moveBoat(int $x, int $y, BoatRepository $boatRepository, EntityManagerInterface $em): Response
    {
        $boat = $boatRepository->findOneBy([]);
        $boat->setCoordX($x);
        $boat->setCoordY($y);

        $em->flush();

        return $this->redirectToRoute('map');
    }

    /**
     * Move the boat to coord N, E, S, W
     * @Route("/direction/{direction}", name="directBoat", requirements={"direction" = "[NSEW]"})
     */
    public function moveDirection(string $direction, BoatRepository $boatRepository, EntityManagerInterface $em, Request $request, MapManager $mapManager): Response
    {
//        Retrieve boat's coordinates
        $boat = $boatRepository->findOneBy([]);
        $x = $boat->getCoordX();
        $y = $boat->getCoordY();

        if (empty($direction)) {
            $this->addFlash('warning', "It seems Jack's compass is broken... Please try to navigate again.");

            return $this->redirectToRoute('map');
        }

//      If all verifications passed, run algorithm depending on the specified direction. Display appropriate flash message.
        switch ($direction) {
            case 'S':
                if ($mapManager->tileExists($x, $y + 1)) {
                    $boat->setCoordY($y + 1);
                } else {
                    $this->addFlash('warning', 'Do you want Jack to drown...?');
                }
                break;
            case 'N':
                if ($mapManager->tileExists($x, $y - 1)) {
                    $boat->setCoordY($y - 1);
                } else {
                    $this->addFlash('warning', 'Do you want Jack to drown...?');
                }
                break;
            case 'W':
                if ($mapManager->tileExists($x - 1, $y)) {
                    $boat->setCoordX($x - 1);
                } else {
                    $this->addFlash('warning', 'Do you want Jack to drown...?');
                }
                break;
            case 'E':
                if ($mapManager->tileExists($x + 1, $y)) {
                    $boat->setCoordX($x + 1);
                } else {
                    $this->addFlash('warning', 'Do you want Jack to drown...?');
                }
                break;
        }

        $em->flush();

//        Check island's treasure and display appropriate flash message if found
        if ($mapManager->checkTreasure($boat)) {
            $this->addFlash('success', 'Congrats ! You have found the lost treasure of Rackham the Red !');
        }

        return $this->redirectToRoute('map');
    }


    /**
     * @Route("/", name="boat_index", methods="GET")
     */
    public
    function index(BoatRepository $boatRepository): Response
    {
        return $this->render('boat/index.html.twig', ['boats' => $boatRepository->findAll()]);
    }

    /**
     * @Route("/new", name="boat_new", methods="GET|POST")
     */
    public
    function new(Request $request): Response
    {
        $boat = new Boat();
        $form = $this->createForm(BoatType::class, $boat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($boat);
            $em->flush();

            return $this->redirectToRoute('boat_index');
        }

        return $this->render('boat/new.html.twig', [
            'boat' => $boat,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="boat_show", methods="GET")
     */
    public function show(Boat $boat): Response
    {
        return $this->render('boat/show.html.twig', ['boat' => $boat]);
    }

    /**
     * @Route("/{id}/edit", name="boat_edit", methods="GET|POST")
     */
    public function edit(Request $request, Boat $boat): Response
    {
        $form = $this->createForm(BoatType::class, $boat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('boat_index', ['id' => $boat->getId()]);
        }

        return $this->render('boat/edit.html.twig', [
            'boat' => $boat,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="boat_delete", methods="DELETE")
     */
    public function delete(Request $request, Boat $boat): Response
    {
        if ($this->isCsrfTokenValid('delete' . $boat->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($boat);
            $em->flush();
        }

        return $this->redirectToRoute('boat_index');
    }
}
