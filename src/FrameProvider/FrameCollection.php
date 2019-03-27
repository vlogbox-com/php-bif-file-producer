<?php

declare(strict_types=1);

namespace BifFileProducer\FrameProvider;

class FrameCollection
{

    /**
     * @var array
     */
    private $frames;
    /**
     * @var int
     */
    private $intervalBetweenFrames;

    public function __construct(
        array $frames,
        int $intervalBetweenFrames
    )
    {
        $this->frames = $frames;
        $this->intervalBetweenFrames = $intervalBetweenFrames;
    }

    /**
     * @return int
     */
    public function getIntervalBetweenFrames(): int
    {
        return $this->intervalBetweenFrames;
    }

    /**
     * @return array
     */
    public function getFrames(): array
    {
        return $this->frames;
    }
}