<?php

declare(strict_types=1);

namespace BifFileProducer\FrameProvider;


interface FrameProviderInterface
{

    public function getFrames(
        string $videoFilePath
    ): FrameCollection;

    public function setIntervalBetweenFrames(int $milliseconds): void;

    public function setFrameWidth(int $width): void;

    public function setOutputDirectory(string $directoryPath): void;
}