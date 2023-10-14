<?php

namespace App\Service;

use App\Entity\Boat;
use App\Entity\Tile;
use App\Repository\BoatRepository;
use App\Repository\TileRepository;
use Doctrine\ORM\EntityManagerInterface;

class MapManager
{
    private TileRepository $tileRepository;
    private BoatRepository $boatRepository;

    public function __construct(TileRepository $tileRepository, BoatRepository $boatRepository)
    {
        $this->tileRepository = $tileRepository;
        $this->boatRepository = $boatRepository;
    }

    public function tileExists(int $x, int $y): bool
    {
        $tile = $this->tileRepository->findOneBy(['coordX' => $x, 'coordY' => $y]);

        if (!empty($tile)) {
            return true;
        } else {
            return false;
        }
    }

    public function getRandomIsland(): Tile
    {
        $tiles = $this->tileRepository->findBy(['type' => 'island']);
        $index = array_rand($tiles);

        $randomTile = $tiles[$index];

        return $randomTile;
    }

    public function checkTreasure(Boat $boat): bool
    {
        $boatX = $boat->getCoordX();
        $boatY = $boat->getCoordY();

        $tile = $this->tileRepository->findOneBy(['coordX' => $boatX, 'coordY' => $boatY]);

//        dd($boat, $tile);

        if (empty($tile) || $tile->getHasTreasure() === false) {
            return false;
        } else {
            return true;
        }

    }
}