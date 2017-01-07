<?php

/*
 * A PSR7 aware cURL client (https://github.com/juliangut/spiral).
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/spiral
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Spiral\Tests;

use Jgut\Spiral\StringStream;

/**
 * String stream tests.
 */
class StringStreamTest extends \PHPUnit_Framework_TestCase
{
    private static function throws($stream, $method, $param = [])
    {
        try {
            call_user_func_array([$stream, $method], $param);
        } catch (\RuntimeException $exception) {
            return true;
        }

        return false;
    }

    public function testRead()
    {
        $stream = new StringStream('This is a string');

        static::assertTrue($stream->isReadable());
        static::assertEquals('This is', $stream->read(7));
        static::assertEquals(' a string', $stream->getContents());
        static::assertEquals('This is a string', (string) $stream);
    }

    public function testSeek()
    {
        $stream = new StringStream('This is a fourty characters long string.');

        static::assertTrue($stream->isSeekable());
        static::assertEquals(40, $stream->getSize());

        $stream->seek(10);

        static::assertEquals(10, $stream->tell());
        $stream->seek(-2, SEEK_CUR);

        static::assertFalse($stream->eof());
        static::assertEquals(8, $stream->tell());

        static::assertEquals(40, strlen((string) $stream));
        static::assertTrue($stream->eof());

        $stream->seek(-12, SEEK_END);
        static::assertEquals(28, $stream->tell());
        static::assertEquals('long string.', $stream->read(999));

        $stream->rewind();
        static::assertEquals(0, $stream->tell());
    }

    public function testWrite()
    {
        $stream = new StringStream('A stream');

        static::assertFalse($stream->isWritable());
        static::assertTrue(self::throws($stream, 'write', ['Test']));

        static::assertEquals('A stream', (string) $stream);
    }

    public function testClose()
    {
        $stream = new StringStream('A stream');
        $stream->close();

        static::assertTrue($stream->eof());
        static::assertEquals('', (string) $stream);
        static::assertFalse($stream->isReadable());
        static::assertFalse($stream->isSeekable());
        static::assertEquals(null, $stream->getSize());
        static::assertTrue(self::throws($stream, 'tell'));
        static::assertTrue(self::throws($stream, 'seek', [2]));
    }

    public function testDetach()
    {
        $stream = new StringStream('A stream');

        static::assertEquals(null, $stream->detach());
    }

    public function testMetadata()
    {
        $stream = new StringStream('A stream');
        $meta = $stream->getMetadata();

        static::assertArrayHasKey('blocked', $meta);
        static::assertArrayHasKey('uri', $meta);

        static::assertEquals($stream->eof(), $meta['eof']);
        static::assertEquals($stream->isSeekable(), $meta['seekable']);

        static::assertNotNull($stream->getMetadata('timed_out'));
        static::assertNotNull($stream->getMetadata('unread_bytes'));

        static::assertNull($stream->getMetadata('non existing key'));
    }
}
