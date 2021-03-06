<?php

/*
 * A PSR7 aware cURL client (https://github.com/juliangut/spiral).
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/spiral
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\Spiral\Tests\Option;

use Jgut\Spiral\Option\OptionFile;

/**
 * File option tests.
 */
class OptionFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Jgut\Spiral\Exception\OptionException
     */
    public function testNoAccess()
    {
        $option = new OptionFile(CURLOPT_COOKIEFILE);

        static::assertEquals(CURLOPT_COOKIEFILE, $option->getOption());
        static::assertEquals('', $option->getValue());

        $option->setValue('fake_file');
    }

    public function testAccessors()
    {
        $file = sys_get_temp_dir() . '/JgutSpiralOptionFile';
        touch($file);

        $option = new OptionFile(CURLOPT_COOKIEFILE);

        $option->setValue($file);
        static::assertEquals($file, $option->getValue());

        unlink($file);
    }
}
