<?php

namespace App\Service;

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

        if (!empty($tile)) {
            return true;
        } else {
            return false;
        }
    }

}