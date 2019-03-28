<?php

declare(strict_types=1);

namespace BifFileProducer\FrameProvider;

use BifFileProducer\FrameProvider\FrameResizer\FrameResizerInterface;

class ScalableFrameProvider implements FrameProviderInterface
{

    /**
     * @var FrameProviderInterface
     */
    private $decorated;

    /**
     * @var int
     */
    private $initialWidth;

    /**
     * @var int
     */
    private $width;

    /**
     * @var FrameCollection[]
     */
    private $cachedCollections = [];
    /**
     * @var string
     */
    private $outputDir;
    /**
     * @var FrameResizerInterface
     */
    private $frameResizer;

    public function __construct(
        FrameProviderInterface $decorated,
        FrameResizerInterface $frameResizer,
        int $initialWidth,
        string $outputDir
    )
    {
        $this->decorated = $decorated;
        $this->initialWidth = $initialWidth;
        $this->outputDir = rtrim($outputDir, DIRECTORY_SEPARATOR);
        $this->frameResizer = $frameResizer;
    }

    public function getFrames(
        string $videoFilePath
    ): FrameCollection
    {

        if (!array_key_exists($videoFilePath, $this->cachedCollections)) {

            $this->cachedCollections[$videoFilePath] = [];

            $this->decorated->setFrameWidth($this->initialWidth);
            $this->decorated->setOutputDirectory($this->outputDir . DIRECTORY_SEPARATOR . $this->width);

            $this->cachedCollections[$videoFilePath][$this->initialWidth] = $this->decorated->getFrames($videoFilePath);
        }

        if (
            array_key_exists($this->width, $this->cachedCollections[$videoFilePath])
        ) {
            return $this->cachedCollections[$videoFilePath][$this->width];
        }

        $this->cachedCollections[$videoFilePath][$this->width] = $this
            ->rescaleCollection(
                $this->cachedCollections[$videoFilePath][$this->initialWidth],
                $this->width
            );

        return $this->cachedCollections[$videoFilePath][$this->width];
    }

    protected function rescaleCollection(FrameCollection $frameCollection, int $width): FrameCollection
    {
        $newFrames = [];
        $this->frameResizer->setOutputDirectory(
            $this->outputDir . DIRECTORY_SEPARATOR . $width
        );
        foreach ($frameCollection->getFrames() as $frame) {
            $fileNameParts = explode(DIRECTORY_SEPARATOR, $frame);
            $fileName = array_pop($fileNameParts);
            $newFrames[] = $this->frameResizer->resize($frame, $width, $fileName);
        }

        return new FrameCollection(
            $newFrames,
            $frameCollection->getIntervalBetweenFrames()
        );
    }

    public function setIntervalBetweenFrames(int $milliseconds): void
    {
        $this->decorated = $milliseconds;
        $this->cachedCollections = [];
    }

    public function setFrameWidth(int $width): void
    {
        $this->width = $width;
        $this->cachedCollections = [];
    }

    public function setOutputDirectory(string $directoryPath): void
    {
        $this->outputDir = rtrim($directoryPath, DIRECTORY_SEPARATOR);
    }
}