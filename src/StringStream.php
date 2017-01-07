<?php

/*
 * A PSR7 aware cURL client (https://github.com/juliangut/spiral).
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/spiral
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Spiral;

use Psr\Http\Message\StreamInterface;

/**
 * Read-only string based PSR-7 Stream.
 *
 * Implements a very basic variant of the PSR-7 StreamInterface
 * using a string passed on during construction.  The stream is
 * read-only and seekable.
 *
 * While this stream supports all the read and seek related
 * interfaces, the preferred method for getting the data contained
 * in a StringStream should be the (string) cast, which has zero
 * overhead in terms of both memory usage and computing resources.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class StringStream implements StreamInterface
{
    /**
     * Stream contents.
     *
     * @var string
     */
    private $contents;

    /**
     * Current stream position.
     *
     * @var string
     */
    private $position;

    /**
     * Create string stream.
     *
     * @param string $contents
     */
    public function __construct($contents)
    {
        $this->contents = (string) $contents;
        $this->position = 0;
    }

    /**
     * Return the underlying string contents of the string stream.
     *
     * @return string
     */
    public function __toString()
    {
        // Seek to the end to simulate having read the stream
        $this->position = strlen($this->contents);

        // Note that when the stream has been closed, contents is
        // set to null, necessitating a cast here.
        return (string) $this->contents;
    }

    /**
     * Simulate closing the stream.
     *
     * This frees up the memory used to hold the contents of the
     * stream, and makes it unreadable afterwards.
     *
     * @return void
     */
    public function close()
    {
        // Free up the memory used to hold the contents and mark
        // the stream as closed.
        $this->contents = null;
        $this->position = 0;
    }

    /**
     * Make stream unusable.
     *
     * Implemented for compatibilty with the StreamInterface.  Since
     * StringStream doesn't have any underlying resources, this always
     * returns null, with the side effect of also calling close on the
     * stream.  This is to satisfy the requirement of the stream being
     * unusable after this method is called.
     *
     * @return null
     */
    public function detach()
    {
        $this->close();
        return null;
    }

    /**
     * Get the size of the stream if it has not been closed.
     *
     * @return int|null The size in bytes if not closed, null otherwise.
     */
    public function getSize()
    {
        if ($this->contents === null) {
            return null;
        }

        return strlen($this->contents);
    }

    /**
     * Returns the current read position in the stream.
     *
     * @throws \RuntimeException if the stream has been closed
     *
     * @return int Read position in the stream
     */
    public function tell()
    {
        if ($this->contents === null) {
            throw new \RuntimeException('Cannot tell position, stream closed');
        }

        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        // if the stream is closed, this will return true
        return $this->position === strlen($this->contents);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return $this->contents !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if ($this->contents === null) {
            throw new \RuntimeException('Cannot seek, stream closed');
        }

        if ($whence === SEEK_SET) {
            $from = 0;
        } elseif ($whence === SEEK_CUR) {
            $from = $this->position;
        } elseif ($whence === SEEK_END) {
            $from = strlen($this->contents);
        } else {
            throw new \RuntimeException('Invalid value to argument $whence');
        }

        $newPosition = $from + (int) $offset;

        if ($newPosition < 0) {
            throw new \RuntimeException('Cannot seek before the beginning');
        }

        $this->position = min($newPosition, strlen($this->contents));
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        return $this->seek(0);
    }

    /**
     * Returns false, string streams are not writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * Throws RuntimeError, string streams are not writable.
     *
     * @param string $string ignored
     *
     * @throws \RuntimeException always, non-writable stream
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function write($string)
    {
        throw new \RuntimeException('Stream is not writable');
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return $this->contents !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        if ((int) $length < 0) {
            throw new \RuntimeException('Cannot read negative length');
        }

        $start = $this->position;
        $this->seek($length, SEEK_CUR);
        return substr($this->contents, $start, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        return $this->read(strlen($this->contents) - $this->position);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        $data = [
            'timed_out' => false,
            'blocked' => false, // nothing in string stream blocks
            'eof' => $this->eof(),
            'unread_bytes' => 0, // nothing is buffered
            'stream_type' => 'string',
            'wrapper_data' => null,
            'seekable' => $this->isSeekable(),
            'uri' => '', // XXX use some placholder here?
        ];

        if ($key === null) {
            return $data;
        } elseif (array_key_exists($key, $data)) {
            return $data[$key];
        } else {
            return null;
        }
    }
}
