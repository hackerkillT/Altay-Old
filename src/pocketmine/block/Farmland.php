<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

class Farmland extends Transparent{

	protected $id = self::FARMLAND;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Farmland";
	}

	public function getHardness() : float{
		return 0.6;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_SHOVEL;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return new AxisAlignedBB(0, 0, 0, 1, 1, 1); //TODO: y max should be 0.9375, but MCPE currently treats them as a full block (https://bugs.mojang.com/browse/MCPE-12109)
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_UP)->isSolid()){
			$this->level->setBlock($this, BlockFactory::get(Block::DIRT), true);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if(!$this->canHydrate()){
			if($this->meta > 0){
				$this->meta--;
				$this->level->setBlock($this, $this, false, false);
			}else{
				$this->level->setBlock($this, BlockFactory::get(Block::DIRT), false, true);
			}
		}elseif($this->meta < 7){
			$this->meta = 7;
			$this->level->setBlock($this, $this, false, false);
		}
	}

	protected function canHydrate() : bool{
		//TODO: check rain
		$start = $this->add(-4, 0, -4);
		$end = $this->add(4, 1, 4);
		for($y = $start->y; $y <= $end->y; ++$y){
			for($z = $start->z; $z <= $end->z; ++$z){
				for($x = $start->x; $x <= $end->x; ++$x){
					$id = $this->level->getBlockIdAt($x, $y, $z);
					if($id === Block::STILL_WATER or $id === Block::FLOWING_WATER){
						return true;
					}
				}
			}
		}

		return false;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get(Item::DIRT)
		];
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}

	public function onEntityFallenUpon(Entity $entity, float $fallDistance) : void{
		if($entity instanceof Living){
			if($this->level->random->nextFloat() < ($fallDistance - 0.5)){
				//TODO: Add MobGrifeing game rule check
				$this->level->setBlock($this, BlockFactory::get(Block::DIRT), true);
			}
		}
	}
}