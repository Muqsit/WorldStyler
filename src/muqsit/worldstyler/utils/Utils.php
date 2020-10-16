<?php

declare(strict_types=1);
namespace muqsit\worldstyler\utils;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\utils\TreeType;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\world\World;

class Utils {

    const FILESIZES = 'BKMGTP';

    public static function humanFilesize(string $file, int $decimals = 2) : string
    {
        //from https://stackoverflow.com/questions/15188033/human-readable-file-size but customized a bit
        $bytes = (string) filesize($file);
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . (self::FILESIZES[$factor] ?? "");
    }

    public static function updateChunks(World $world, int $minChunkX, int $maxChunkX, int $minChunkZ, int $maxChunkZ) : void
    {
        for ($chunkX = $minChunkX; $chunkX <= $maxChunkX; ++$chunkX) {
            for ($chunkZ = $minChunkZ; $chunkZ <= $maxChunkZ; ++$chunkZ) {
                $chunk = $world->getOrLoadChunk($chunkX, $chunkZ);
                $chunk->setDirty();
                $world->setChunk($chunkX, $chunkZ, $chunk, false);
            }
        }
    }

    public static function getBlockFromString(string $block) : ?Block
    {
        try {
            return LegacyStringToItemParser::getInstance()->parse($block)->getBlock();
        } catch (\InvalidArgumentException $e) {
            $data = explode(":", $block, 3);
            return BlockFactory::getInstance()->get((int) $data[0], (int) ($data[1] ?? 0));
        }
    }

    public static function getPCMapping() : BlockToBlockMapping
    {
        $factory = BlockFactory::getInstance();
        $mapping = new BlockToBlockMapping();

        for ($meta = 0; $meta < 16; ++$meta) {
            $mapping->add($factory->get(BlockLegacyIds::ACTIVATOR_RAIL, $meta), $factory->get(BlockLegacyIds::WOODEN_SLAB, $meta));
            $mapping->add($factory->get(BlockLegacyIds::INVISIBLE_BEDROCK, $meta), $factory->get(BlockLegacyIds::STAINED_GLASS, $meta));
            $mapping->add($factory->get(BlockLegacyIds::DROPPER, $meta), $factory->get(BlockLegacyIds::DOUBLE_WOODEN_SLAB, $meta));
            $mapping->add($factory->get(BlockLegacyIds::REPEATING_COMMAND_BLOCK, $meta), $factory->get(BlockLegacyIds::FENCE, TreeType::SPRUCE()->getMagicNumber()));
            $mapping->add($factory->get(BlockLegacyIds::CHAIN_COMMAND_BLOCK, $meta), $factory->get(BlockLegacyIds::FENCE, TreeType::BIRCH()->getMagicNumber()));
            $mapping->add($factory->get(BlockLegacyIds::HARD_GLASS_PANE, $meta), $factory->get(BlockLegacyIds::FENCE, TreeType::JUNGLE()->getMagicNumber()));
            $mapping->add($factory->get(BlockLegacyIds::HARD_STAINED_GLASS_PANE, $meta), $factory->get(BlockLegacyIds::FENCE, TreeType::DARK_OAK()->getMagicNumber()));
            $mapping->add($factory->get(BlockLegacyIds::CHEMICAL_HEAT, $meta), $factory->get(BlockLegacyIds::FENCE, TreeType::ACACIA()->getMagicNumber()));
            $mapping->add($factory->get(BlockLegacyIds::GLOW_STICK, $meta), $factory->get(BlockLegacyIds::BARRIER, $meta));
        }

        $mapping->add($factory->get(BlockLegacyIds::DOUBLE_STONE_SLAB, 6), $factory->get(BlockLegacyIds::DOUBLE_STONE_SLAB, 7));
        $mapping->add($factory->get(BlockLegacyIds::DOUBLE_STONE_SLAB, 7), $factory->get(BlockLegacyIds::DOUBLE_STONE_SLAB, 6));

        $mapping->add($factory->get(BlockLegacyIds::STONE_SLAB, 6), $factory->get(BlockLegacyIds::STONE_SLAB, 7));
        $mapping->add($factory->get(BlockLegacyIds::STONE_SLAB, 7), $factory->get(BlockLegacyIds::STONE_SLAB, 6));
        $mapping->add($factory->get(BlockLegacyIds::STONE_SLAB, 15), $factory->get(BlockLegacyIds::STONE_SLAB, 14));

        foreach([BlockLegacyIds::TRAPDOOR, BlockLegacyIds::IRON_TRAPDOOR] as $blockId){
            $mapping->add($factory->get($blockId, 0), $factory->get($blockId, 3));
            $mapping->add($factory->get($blockId, 1),$factory->get($blockId, 2));
            $mapping->add($factory->get($blockId, 2),$factory->get($blockId, 1));
            $mapping->add($factory->get($blockId, 3), $factory->get($blockId, 0));
            $mapping->add($factory->get($blockId, 4), $factory->get($blockId, 7));
            $mapping->add($factory->get($blockId, 5), $factory->get($blockId, 6));
            $mapping->add($factory->get($blockId, 6), $factory->get($blockId, 5));
            $mapping->add($factory->get($blockId, 7), $factory->get($blockId, 4));
            $mapping->add($factory->get($blockId, 8), $factory->get($blockId, 11));
            $mapping->add($factory->get($blockId, 9), $factory->get($blockId, 10));
            $mapping->add($factory->get($blockId, 10), $factory->get($blockId, 9));
            $mapping->add($factory->get($blockId, 11), $factory->get($blockId, 8));
            $mapping->add($factory->get($blockId, 12), $factory->get($blockId, 15));
            $mapping->add($factory->get($blockId, 13), $factory->get($blockId, 14));
            $mapping->add($factory->get($blockId, 14), $factory->get($blockId, 13));
            $mapping->add($factory->get($blockId, 15), $factory->get($blockId, 12));
        }
        return $mapping;
    }
}
