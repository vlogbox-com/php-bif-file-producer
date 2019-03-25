<?php

declare(strict_types=1);

namespace BifFileProducer;

use BifFileProducer\Factory\BifFileFactory;
use BifFileProducer\FrameProvider\FFmpegFrameProvider;
use BifFileProducer\FrameProvider\FrameProviderInterface;
use BifFileProducer\FrameProvider\FrameResizer\FFmpegResizer;
use BifGenerator\FrameProvider\ScalableFrameProvider;

class Builder
{

    public const TYPE_SD = 240;
    public const TYPE_HD = 320;
    public const TYPE_FHD = 480; //experimental

    protected const VALID_TYPES = [
        self::TYPE_SD,
        self::TYPE_HD,
        self::TYPE_FHD
    ];

    /**
     * @var FrameProviderInterface|null
     */
    private $frameProvider;

    /**
     * @var BifFileFactory
     */
    private $bifFactory;

    /**
     * @var array
     */
    private $typesToProduce;

    private $intervalBetweenFramesInMilliseconds = 10000;

    private $outputDir;

    private $tempDir;

    public function __construct()
    {
        $this->bifFactory = new BifFileFactory();
        $this->typesToProduce = self::VALID_TYPES;
        $this->tempDir = sys_get_temp_dir();
    }

    public function setFrameProvider(FrameProviderInterface $frameProvider): self
    {
        $this->frameProvider = $frameProvider;
        return $this;
    }

    public function setIntervalBetweenFrames(int $milliseconds): self
    {
        if (1000 > $milliseconds) {
            throw new \BadMethodCallException('Interval couldn\'t be less than 1000 milliseconds');
        }

        $this->intervalBetweenFramesInMilliseconds = $milliseconds;
        return $this;
    }

    public function setTypesToProduce(array $types): self
    {
        if (!$types) {
            throw new \BadMethodCallException('Types of BIF file to produce cannot be empty');
        }

        foreach ($types as $type) {
            if (
                !\in_array(
                    $type,
                    self::VALID_TYPES,
                    true
                )
            ) {
                throw new \BadMethodCallException('Invalid BIF type: ' . $type);
            }
        }

        $this->typesToProduce = $types;
        return $this;
    }

    public function setOutputDirectory(string $directoryPath): self
    {
        $this->outputDir = rtrim($directoryPath, DIRECTORY_SEPARATOR);
        if (
            !mkdir($this->outputDir)
            && !is_dir($this->outputDir)
        ) {
            throw new \BadMethodCallException('Output folder not exists and could not be created: ' . $directoryPath);
        }

        return $this;
    }

    public function setTempDirectory(string $directoryPath): self
    {
        $this->tempDir = rtrim($directoryPath, DIRECTORY_SEPARATOR);
        if (
            !mkdir($this->tempDir)
            && !is_dir($this->tempDir)
        ) {
            throw new \BadMethodCallException('Output folder not exists and could not be created: ' . $directoryPath);
        }

        return $this;
    }



    protected function getDefaultFrameProvider(string $temporaryDirPath): FrameProviderInterface
    {
        $frameDir = $temporaryDirPath . DIRECTORY_SEPARATOR . 'frames';

        $maxDifinition = max(...$this->typesToProduce);
        return new ScalableFrameProvider(
            new FFmpegFrameProvider($frameDir),
            new FFmpegResizer($frameDir),
            $maxDifinition,
            $frameDir
        );
    }


    public function createBifForVideo(string $videoFilePath): array
    {
        $dropTmpDir = false;
        $tmpDir = null;
        $frameProvider = $this->frameProvider;
        if (!$frameProvider) {
            $tmpDir = $this->tempDir . DIRECTORY_SEPARATOR . md5(microtime(true));

            if (
                !mkdir($tmpDir)
                && !is_dir($tmpDir)
            ) {
                throw new \BadMethodCallException('Tmp folder not writeable: ' . $this->tempDir);
            }
            $dropTmpDir = true;
            $frameProvider = $this->getDefaultFrameProvider($tmpDir);
        }

        $result = [];

        foreach ($this->typesToProduce as $type) {
            $frameProvider->setFrameWidth($type);
            $result[$type] = $this
                ->bifFactory
                ->create(
                    $frameProvider->getFrames($videoFilePath),
                    $this->outputDir . DIRECTORY_SEPARATOR . $type . '.bif'
                );
        }

        if($dropTmpDir && $tmpDir) {
            rmdir($tmpDir);
        }

        return $result;
    }


}