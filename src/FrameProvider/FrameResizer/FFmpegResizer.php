<?php

declare(strict_types=1);

namespace BifFileProducer\FrameProvider\FrameResizer;

class FFmpegResizer implements FrameResizerInterface
{
    /**
     * @var string
     */
    private $binary;
    /**
     * @var string
     */
    private $outputDir;

    public function __construct(
        string $outputDir,
        string $binaryPath = null
    )
    {
        $this->binary = 'ffmpeg';

        if($binaryPath) {
            $this->binary = $binaryPath;
        }

        $this->setOutputDirectory($outputDir);
    }

    public function resize(
        string $origin,
        int $width,
        string $outputName
    ): bool
    {
        $outputFileName = $this->outputDir . DIRECTORY_SEPARATOR . $outputName;
        $command =
            $this->binary
            . ' -i ' . $origin
            . ' -vf scale=' . $width . ':-1'
            . ' ' . $outputFileName;
        exec($command);

        return file_exists($outputFileName);
    }

    public function setOutputDirectory(string $directoryPath): void
    {
        $this->outputDir = $directoryPath;
        /**
         * Note: https://github.com/kalessil/phpinspectionsea/blob/master/docs/probable-bugs.md#mkdir-race-condition
         */
        /** @noinspection NotOptimalIfConditionsInspection */
        if (
            !is_dir($this->outputDir)
            && !mkdir($this->outputDir, 0777, true)
            && !is_dir($this->outputDir)
        ) {
            throw new \BadMethodCallException('Output folder not exists and could not be created: ' . $directoryPath);
        }
    }
}