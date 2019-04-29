<?php

declare(strict_types=1);
namespace muqsit\worldstyler\utils;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\WoodenFence;
use pocketmine\level\Level;

class Utils {

    const REPLACEMENTS = [
        Block::ACTIVATOR_RAIL => [
            -1 => [Block::WOODEN_SLAB, null]//-1 = all block metas, null = dont change the block's meta.
        ],
        Block::INVISIBLE_BEDROCK => [
            -1 => [Block::STAINED_GLASS, null]
        ],
        Block::DROPPER => [
            -1 => [Block::DOUBLE_WOODEN_SLAB, null]
        ],
        188 => [
            -1 => [Block::FENCE, WoodenFence::FENCE_SPRUCE]
        ],
        189 => [
            -1 => [Block::FENCE, WoodenFence::FENCE_BIRCH]
        ],
        190 => [
            -1 => [Block::FENCE, WoodenFence::FENCE_JUNGLE]
        ],
        191 => [
            -1 => [Block::FENCE, WoodenFence::FENCE_DARKOAK]
        ],
        192 => [
            -1 => [Block::FENCE, WoodenFence::FENCE_ACACIA]
        ],
        Block::DOUBLE_STONE_SLAB => [
            6 => [Block::DOUBLE_STONE_SLAB, 7],
            7 => [Block::DOUBLE_STONE_SLAB, 6],
        ],
        Block::STONE_SLAB => [
            6 => [Block::STONE_SLAB, 7],
            7 => [Block::STONE_SLAB, 6],
            15 => [Block::STONE_SLAB, 14]
        ],
        Block::TRAPDOOR => [
            0 => [null, 3],
            1 => [null, 2],
            2 => [null, 1],
            3 => [null, 0],
            4 => [null, 7],
            5 => [null, 6],
            6 => [null, 5],
            7 => [null, 4],
            8 => [null, 11],
            9 => [null, 10],
            10 => [null, 9],
            11 => [null, 8],
            12 => [null, 15],
            13 => [null, 14],
            14 => [null, 13],
            15 => [null, 12]
        ],
        Block::IRON_TRAPDOOR => [
            0 => [null, 3],
            1 => [null, 2],
            2 => [null, 1],
            3 => [null, 0],
            4 => [null, 7],
            5 => [null, 6],
            6 => [null, 5],
            7 => [null, 4],
            8 => [null, 11],
            9 => [null, 10],
            10 => [null, 9],
            11 => [null, 8],
            12 => [null, 15],
            13 => [null, 14],
            14 => [null, 13],
            15 => [null, 12]
        ],
        166 => [
            -1 => [Block::INVISIBLE_BEDROCK, null]
        ],
        202 => [
            -1 => [Block::PURPUR_BLOCK, null]
        ]
    ];

    const FILESIZES = 'BKMGTP';

    public static function humanFilesize(string $file, int $decimals = 2) : string
    {
        //from https://stackoverflow.com/questions/15188033/human-readable-file-size but customized a bit
        $bytes = (string) filesize($file);
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . (self::FILESIZES[$factor] ?? "");
    }

    public static function updateChunks(Level $level, int $minChunkX, int $maxChunkX, int $minChunkZ, int $maxChunkZ) : void
    {
        for ($chunkX = $minChunkX; $chunkX <= $maxChunkX; ++$chunkX) {
            for ($chunkZ = $minChunkZ; $chunkZ <= $maxChunkZ; ++$chunkZ) {
                $level->setChunk($chunkX, $chunkZ, $level->getChunk($chunkX, $chunkZ), false);
            }
        }
    }

    public static function getBlockFromString(string $block) : ?Block
    {
        $blockdata = explode(":", $block, 2);
        $data = array_map("intval", $blockdata);

        $name = strtolower($blockdata[0]);
        foreach (BlockFactory::getBlockStatesArray() as $bl) {
            if (strtolower($bl->getName()) === $name) {
                return Block::get($bl->getId(), $data[1] ?? $bl->getDamage());
            }
        }

        return Block::get(...$data);
    }
}
