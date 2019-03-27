<?php

declare(strict_types=1);

namespace BifFileProducer\Factory;

use BifFileProducer\FrameProvider\FrameCollection;

interface BifFileFactoryInterface
{

    /**
     * @param FrameCollection $frameCollection
     * @param string $outputFile
     * @return bool
     */
    public function create(FrameCollection $frameCollection, string $outputFile): bool;
}