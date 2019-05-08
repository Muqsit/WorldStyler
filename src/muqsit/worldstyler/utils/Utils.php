<?php

declare(strict_types=1);
namespace muqsit\worldstyler\utils;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\utils\TreeType;
use pocketmine\world\World;
use pocketmine\item\ItemFactory;

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
                foreach ($world->getChunkListeners($chunkX, $chunkZ) as $loader) {
                    $world->setChunk($chunkX, $chunkZ, $world->getChunk($chunkX, $chunkZ), false);
                }
            }
        }
    }

    public static function getBlockFromString(string $block) : ?Block
    {
        try {
            return ItemFactory::fromString($block)->getBlock();
        } catch (\InvalidArgumentException $e) {
            $data = explode(":", $block, 3);
            return BlockFactory::get((int) $data[0], (int) ($data[1] ?? 0));
        }
    }

    public static function getPCMapping() : BlockToBlockMapping
    {
        $mapping = new BlockToBlockMapping();

        for ($meta = 0; $meta < 16; ++$meta) {
            $mapping->add(Block::get(Block::ACTIVATOR_RAIL, $meta), Block::get(Block::WOODEN_SLAB, $meta));
            $mapping->add(Block::get(Block::INVISIBLE_BEDROCK, $meta), Block::get(Block::STAINED_GLASS, $meta));
            $mapping->add(Block::get(Block::DROPPER, $meta), Block::get(Block::DOUBLE_WOODEN_SLAB, $meta));
            $mapping->add(Block::get(Block::REPEATING_COMMAND_BLOCK, $meta), Block::get(Block::FENCE, TreeType::SPRUCE()->getMagicNumber()));
            $mapping->add(Block::get(Block::CHAIN_COMMAND_BLOCK, $meta), Block::get(Block::FENCE, TreeType::BIRCH()->getMagicNumber()));
            $mapping->add(Block::get(Block::HARD_GLASS_PANE, $meta), Block::get(Block::FENCE, TreeType::JUNGLE()->getMagicNumber()));
            $mapping->add(Block::get(Block::HARD_STAINED_GLASS_PANE, $meta), Block::get(Block::FENCE, TreeType::DARK_OAK()->getMagicNumber()));
            $mapping->add(Block::get(Block::CHEMICAL_HEAT, $meta), Block::get(Block::FENCE, TreeType::ACACIA()->getMagicNumber()));
            $mapping->add(Block::get(Block::GLOW_STICK, $meta), Block::get(Block::BARRIER, $meta));
        }

        $mapping->add(Block::get(Block::DOUBLE_STONE_SLAB, 6), Block::get(Block::DOUBLE_STONE_SLAB, 7));
        $mapping->add(Block::get(Block::DOUBLE_STONE_SLAB, 7), Block::get(Block::DOUBLE_STONE_SLAB, 6));

        $mapping->add(Block::get(Block::STONE_SLAB, 6), Block::get(Block::STONE_SLAB, 7));
        $mapping->add(Block::get(Block::STONE_SLAB, 7), Block::get(Block::STONE_SLAB, 6));
        $mapping->add(Block::get(Block::STONE_SLAB, 15), Block::get(Block::STONE_SLAB, 14));

        foreach ([Block::TRAPDOOR, Block::IRON_TRAPDOOR] as $blockId) {
            $mapping->add(Block::get($blockId, 0), Block::get($blockId, 3));
            $mapping->add(Block::get($blockId, 1), Block::get($blockId, 2));
            $mapping->add(Block::get($blockId, 2), Block::get($blockId, 1));
            $mapping->add(Block::get($blockId, 3), Block::get($blockId, 0));
            $mapping->add(Block::get($blockId, 4), Block::get($blockId, 7));
            $mapping->add(Block::get($blockId, 5), Block::get($blockId, 6));
            $mapping->add(Block::get($blockId, 6), Block::get($blockId, 5));
            $mapping->add(Block::get($blockId, 7), Block::get($blockId, 4));
            $mapping->add(Block::get($blockId, 8), Block::get($blockId, 11));
            $mapping->add(Block::get($blockId, 9), Block::get($blockId, 10));
            $mapping->add(Block::get($blockId, 10), Block::get($blockId, 9));
            $mapping->add(Block::get($blockId, 11), Block::get($blockId, 8));
            $mapping->add(Block::get($blockId, 12), Block::get($blockId, 15));
            $mapping->add(Block::get($blockId, 13), Block::get($blockId, 14));
            $mapping->add(Block::get($blockId, 14), Block::get($blockId, 13));
            $mapping->add(Block::get($blockId, 15), Block::get($blockId, 12));
        }

        return $mapping;
    }
}
