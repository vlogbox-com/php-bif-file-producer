<?php

declare(strict_types=1);

namespace BifFileProducer\FrameProvider;

class FFmpegFrameProvider implements FrameProviderInterface
{
    /**
     * @var int
     */
    protected $intervalBetweenFramesInMilliseconds = 10000;

    /**
     * @var string
     */
    private $outputDir;

    /**
     * @var string
     */
    private $binary;

    private $width = 240;

    public function __construct(
        string $outputDir,
        string $binaryPath = null
    )
    {
        $this->setOutputDirectory($outputDir);
        $this->binary = 'ffmpeg';

        if($binaryPath) {
            $this->binary = $binaryPath;
        }

        $this->checkFFmpegAvailability();
    }

    protected function checkFFmpegAvailability(): void
    {
        $exitCode = null;
        $output = null;
        exec($this->binary . ' -version', $output, $exitCode);
        $output = implode('.', $output);
        if (
            $exitCode !== 0
            || !preg_match('/ffmpeg version [\d\.\-]+/im', $output)
        ) {
            throw new \RuntimeException('ffmpeg not found. Error output: ' . $output);
        }
    }

    public function getFrames(string $videoFilePath): FrameCollection
    {
        $frameRate = round(
            $this->intervalBetweenFramesInMilliseconds / 1000,
            1
        );

        $command = $this->binary
            . ' -i ' . $videoFilePath
            . ' -r ' . $frameRate
            . ' -vf scale=' . $this->width . ':-1'
            . ' ' . $this->outputDir . DIRECTORY_SEPARATOR . '%08d.jpg';

        $exitCode = null;
        $output = null;
        exec($command, $output, $exitCode);

        if ($exitCode) {
            throw new \RuntimeException('Something went wrong: ' . implode('.', $output));
        }

        $files = \scandir($this->outputDir, SCANDIR_SORT_ASCENDING);
        $files = array_filter(
            $files,
            function (string $path) {
                return !\in_array($path, ['.', '..']);
            }
        );

        $files = array_filter(
            $files,
            function (string $path) {
                return !\in_array($path, ['.', '..']);
            }
        );

        return new FrameCollection(
            array_map(
                function(string $fileName): string
                {
                    return $this->outputDir . $fileName;
                },
                $files
            ),
            $this->intervalBetweenFramesInMilliseconds
        );
    }

    public function setIntervalBetweenFrames(int $milliseconds): void
    {
        if (1000 > $milliseconds) {
            throw new \BadMethodCallException('Interval couldn\'t be less than 1000 milliseconds');
        }

        $this->intervalBetweenFramesInMilliseconds = $milliseconds;
    }

    public function setFrameWidth(int $width): void
    {
        $this->width = $width;
    }

    public function setOutputDirectory(string $directoryPath): void
    {
        $this->outputDir = $directoryPath;
        if (
            !mkdir($this->outputDir)
            && !is_dir($this->outputDir)
        ) {
            throw new \BadMethodCallException('Output folder not exists and could not be created: ' . $directoryPath);
        }
    }
}