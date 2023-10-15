<?php

namespace App\Service;

use App\Entity\Boat;
use App\Entity\Tile;
use App\Repository\TileRepository;

class MapManager
{
    private TileRepository $tileRepository;

    public function __construct(TileRepository $tileRepository)
    {
        $this->tileRepository = $tileRepository;
    }

    public function tileExists(int $x, int $y): bool
    {
        $tile = $this->tileRepository->findOneBy(['coordX' => $x, 'coordY' => $y]);

        if (empty($tile)) {
            return false;
        }

        return true;
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
        $tile = $this->tileRepository->findOneBy(['coordX' => $boat->getCoordX(), 'coordY' => $boat->getCoordY()]);

        if (empty($tile) || $tile->getHasTreasure() === false) {
            return false;
        }

        return true;
    }
}