<?php

declare(strict_types=1);

namespace BifFileProducer\Factory;

use BifFileProducer\FrameProvider\FrameCollection;

/**
 * Class BifFileFactory
 * @package BifFileProducer\Factory
 * @author VlogBox Dev Team <development@vlogbox.com>
 */
class BifFileFactory implements BifFileFactoryInterface
{

    protected const BIF_MAGIC_NUMBERS = [
        '89',
        '42',
        '49',
        '46',
        '0d',
        '0a',
        '1a',
        '0a'
    ];

    public function create(FrameCollection $frameCollection, string $outputFile): bool
    {
        $bifFileHandle = fopen($outputFile, 'wb');

        foreach (self::BIF_MAGIC_NUMBERS as $number) {
            fwrite(
                $bifFileHandle,
                pack('H*', $number)
            );
        }

        fwrite(
            $bifFileHandle,
            pack('L', 0)
        );

        $frames = $frameCollection->getFrames();

        fwrite(
            $bifFileHandle,
            pack('L', \count($frames))
        );

        fwrite(
            $bifFileHandle,
            pack('L', $frameCollection->getIntervalBetweenFrames())
        );

        foreach (range(20, 63) as $number) {
            fwrite(
                $bifFileHandle,
                pack('H*','00')
            );
        }

        $offset = (\count($frames) * 8) + 64 + 8;

        $timestamp = 0;

        $timestampInterval = (int)($frameCollection->getIntervalBetweenFrames() / 1000);

        foreach ($frames as $frame) {
            fwrite(
                $bifFileHandle,
                pack('L', $timestamp)
            );

            $fileSize = filesize($frame);
            fwrite(
                $bifFileHandle,
                pack('L', $offset)
            );
            $offset += $fileSize;
            $timestamp += $timestampInterval;
        }

        fwrite(
            $bifFileHandle,
            pack('H*', 'ffffffff')
        );

        fwrite(
            $bifFileHandle,
            pack('L', $offset)
        );

        foreach ($frames as $frame) {
            $handle = fopen($frame, 'rb');
            $contents = fread($handle, filesize($frame));
            fclose($handle);
            fwrite(
                $bifFileHandle,
                $contents
            );
        }

        fclose($bifFileHandle);
        return true;
    }
}