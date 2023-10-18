<?php

namespace App\Tests;

use App\Entity\Boat;
use App\Entity\Tile;
use App\Repository\BoatRepository;
use App\Repository\TileRepository;
use App\Service\MapManager;
use PHPUnit\Framework\TestCase;

class MapManagerTest extends TestCase
{
    private TileRepository $tileRepository;
    private MapManager $mapManager;
    private Boat $boat;
    private Tile $tile;

    public function __construct()
    {
        parent::__construct();

        $this->tileRepository = $this->createMock(TileRepository::class);
        $this->mapManager = new MapManager($this->tileRepository);
        $this->boat = (new Boat())
            ->setCoordY(1)
            ->setCoordX(1);
        $this->tile = (new Tile())
            ->setCoordX(1)
            ->setCoordY(1);
    }

    public function testTileExists(): void
    {
        $this->tileRepository->method('findOneBy')->willReturn(new Tile());

        $tileExists = $this->mapManager->tileExists(0, 1);

        $this->assertTrue($tileExists);
    }

    public function testTileDoesNotExists(): void
    {
        $this->tileRepository->method('findOneBy')->willReturn(null);

        $tileExists = $this->mapManager->tileExists(-1, -1);

        $this->assertFalse($tileExists);
    }

    public function testGetRandomIsland(): void
    {
        $tile1 = new Tile();
        $tile1->setType('island');

        $tile2 = new Tile();
        $tile2->setType('island');

        $tiles = [$tile1, $tile2];
        $randomIndex = array_rand($tiles);

        $this->tileRepository->method('findBy')
            ->with(['type' => 'island'])
            ->willReturn([$tile1, $tile2]);

        $randomIsland = $this->mapManager->getRandomIsland();

        $this->assertNotNull($tiles[$randomIndex]);
        $this->assertInstanceOf(Tile::class, $tiles[$randomIndex]);
        $this->assertInstanceOf(Tile::class, $randomIsland);
        $this->assertEquals('island', $randomIsland->getType());
    }

    public function testCheckTreasureFound()
    {
        $tile = $this->tile->setHasTreasure(true);

        $boatRepository = $this->createMock(BoatRepository::class);
        $boatRepository->method('findOneBy')->willReturn($this->boat);

        $this->tileRepository->method('findOneBy')
            ->with(['coordX' => $this->boat->getCoordX(), 'coordY' => $this->boat->getCoordY()])
            ->willReturn($tile);

        $treasureFound = $this->mapManager->checkTreasure($this->boat);

        $this->assertTrue($treasureFound);
    }

    public function testCheckTreasureNotFound()
    {
        $tile = $this->tile->setHasTreasure(false);

        $boatRepository = $this->createMock(BoatRepository::class);
        $boatRepository->method('findOneBy')->willReturn($this->boat);

        $this->tileRepository->method('findOneBy')
            ->with(['coordX' => $this->boat->getCoordX(), 'coordY' => $this->boat->getCoordY()])
            ->willReturn($tile);

        $treasureFound = $this->mapManager->checkTreasure($this->boat);

        $this->assertFalse($treasureFound);
    }
}
