<?php

namespace App\Controller;

use App\Service\MapManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Tile;
use App\Repository\BoatRepository;

class MapController extends AbstractController
{
    /**
     * @Route("/map", name="map")
     */
    public function displayMap(BoatRepository $boatRepository, MapManager $mapManager) :Response
    {
        $em = $this->getDoctrine()->getManager();
        $tiles = $em->getRepository(Tile::class)->findAll();

        foreach ($tiles as $tile) {
            $map[$tile->getCoordX()][$tile->getCoordY()] = $tile;
        }

        $boat = $boatRepository->findOneBy([]);

        $currentTileX = $boat->getCoordX();
        $currentTileY = $boat->getCoordY();

        $currentTile = $em->getRepository(Tile::class)->findOneBy(['coordX' => $currentTileX, 'coordY' => $currentTileY]);

//        Setting default value of 'direction' for the BoatController's route parameter
        $direction = 'N';

        $em->flush();

        return $this->render('map/index.html.twig', [
            'map'  => $map ?? [],
            'boat' => $boat,
            'currentTile' => $currentTile,
            'direction' => $direction
        ]);
    }

    /**
     * @Route("/start", name="start")
     */
    public function start(BoatRepository $boatRepository, EntityManagerInterface $em, MapManager $mapManager)
    {
//        Reset boat's coordinates to 0
        $boat = $boatRepository->findOneBy([]);

        $boat->setCoordY(0);
        $boat->setCoordX(0);

//        Reset tile's treasure to 0 and set a new one
        $tiles = $em->getRepository(Tile::class)->findBy(['type' => 'island']);

        foreach ($tiles as $tile) {
            $tile->setHasTreasure(false);
        }

        $randomTile = $mapManager->getRandomIsland();
        $randomTile->setHasTreasure(1);

        $em->flush();

        return $this->redirectToRoute('map');
    }
}
