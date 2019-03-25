<?php

declare(strict_types=1);

namespace BifGenerator\Factory;

use BifGenerator\FrameProvider\FrameCollection;

interface BifFileFactoryInterface
{

    /**
     * @param FrameCollection $frameCollection
     * @param string $outputFile
     * @return bool
     */
    public function create(FrameCollection $frameCollection, string $outputFile): bool;
}