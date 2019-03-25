<?php

declare(strict_types=1);

namespace BifFileProducer\FrameProvider\FrameResizer;

interface FrameResizerInterface
{

    public function resize(
        string $origin,
        int $width,
        string $outputName
    ): bool;

    public function setOutputDirectory(string $directoryPath): void;
}