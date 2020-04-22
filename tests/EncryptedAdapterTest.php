<?php

declare(strict_types=1);

namespace Swis\Flysystem\Encrypted\Tests;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Str;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Config;
use PHPUnit\Framework\TestCase;
use Swis\Flysystem\Encrypted\EncryptedAdapter;

class EncryptedAdapterTest extends TestCase
{
    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        static::tearDownAfterClass();
        $directory = __DIR__.'/_files/write';
        @mkdir($directory);
    }

    /**
     * This method is called after the last test of this test class is run.
     */
    public static function tearDownAfterClass(): void
    {
        $directory = __DIR__.'/_files/write';
        if (is_dir($directory)) {
            array_map('unlink', glob("$directory/*"));
            rmdir($directory);
        }
    }

    /**
     * @test
     * @dataProvider writeData
     *
     * @param string                   $path
     * @param string                   $contents
     * @param \League\Flysystem\Config $config
     */
    public function it_can_write(string $path, string $contents, Config $config)
    {
        // arrange
        $adapter = $this->getDecoratedLocalAdapter('write');
        $encrypter = $this->getEncrypter();

        // act
        $result = $adapter->write($path, $contents, $config);

        // assert
        $this->assertNotFalse($result);

        $fileContents = file_get_contents($adapter->applyPathPrefix($result['path']));
        $this->assertSame($contents, $encrypter->decrypt($fileContents));
    }

    public function writeData(): array
    {
        $config = new Config();

        return [
            ['write-0.txt', Str::random(50), $config],
            ['write-1.txt', Str::random(100), $config],
            ['write-2.txt', Str::random(123), $config],
            ['write-3.txt', Str::random(456), $config],
            ['write-4.txt', Str::random(200), $config],
            ['write-5.txt', Str::random(10), $config],
            ['write-6.txt', Str::random(250), $config],
            ['write-7.txt', Str::random(300), $config],
            ['write-8.txt', Str::random(333), $config],
            ['write-9.txt', Str::random(500), $config],
        ];
    }

    /**
     * @test
     * @dataProvider writeStreamData
     *
     * @param string                   $path
     * @param resource                 $stream
     * @param \League\Flysystem\Config $config
     */
    public function it_can_write_stream(string $path, $stream, Config $config)
    {
        // arrange
        $adapter = $this->getDecoratedLocalAdapter('write');
        $encrypter = $this->getEncrypter();

        // act
        $result = $adapter->writeStream($path, $stream, $config);

        // assert
        $this->assertNotFalse($result);

        fseek($stream, 0);
        $fileContents = file_get_contents($adapter->applyPathPrefix($result['path']));
        $this->assertSame(stream_get_contents($stream), $encrypter->decrypt($fileContents));
    }

    public function writeStreamData(): array
    {
        $config = new Config();

        return array_map(
            static function (array $row) {
                $file = tmpfile();
                fwrite($file, $row[1]);
                fseek($file, 0);

                $row[1] = $file;

                return $row;
            },
            [
                ['write-stream-0.txt', Str::random(50), $config],
                ['write-stream-1.txt', Str::random(100), $config],
                ['write-stream-2.txt', Str::random(123), $config],
                ['write-stream-3.txt', Str::random(456), $config],
                ['write-stream-4.txt', Str::random(200), $config],
                ['write-stream-5.txt', Str::random(10), $config],
                ['write-stream-6.txt', Str::random(250), $config],
                ['write-stream-7.txt', Str::random(300), $config],
                ['write-stream-8.txt', Str::random(333), $config],
                ['write-stream-9.txt', Str::random(500), $config],
            ]
        );
    }

    /**
     * @test
     * @dataProvider updateData
     *
     * @param string                   $path
     * @param string                   $contents
     * @param \League\Flysystem\Config $config
     */
    public function it_can_update(string $path, string $contents, Config $config)
    {
        // arrange
        $adapter = $this->getDecoratedLocalAdapter('write');
        $encrypter = $this->getEncrypter();

        // act
        $result = $adapter->update($path, $contents, $config);

        // assert
        $this->assertNotFalse($result);

        $fileContents = file_get_contents($adapter->applyPathPrefix($result['path']));
        $this->assertSame($contents, $encrypter->decrypt($fileContents));
    }

    public function updateData(): array
    {
        $config = new Config();

        return [
            ['update-0.txt', Str::random(50), $config],
            ['update-1.txt', Str::random(100), $config],
            ['update-2.txt', Str::random(123), $config],
            ['update-3.txt', Str::random(456), $config],
            ['update-4.txt', Str::random(200), $config],
            ['update-5.txt', Str::random(10), $config],
            ['update-6.txt', Str::random(250), $config],
            ['update-7.txt', Str::random(300), $config],
            ['update-8.txt', Str::random(333), $config],
            ['update-9.txt', Str::random(500), $config],
        ];
    }

    /**
     * @test
     * @dataProvider updateStreamData
     *
     * @param string                   $path
     * @param resource                 $stream
     * @param \League\Flysystem\Config $config
     */
    public function it_can_update_stream(string $path, $stream, Config $config)
    {
        // arrange
        $adapter = $this->getDecoratedLocalAdapter('write');
        $encrypter = $this->getEncrypter();

        // act
        $result = $adapter->updateStream($path, $stream, $config);

        // assert
        $this->assertNotFalse($result);

        fseek($stream, 0);
        $fileContents = file_get_contents($adapter->applyPathPrefix($result['path']));
        $this->assertSame(stream_get_contents($stream), $encrypter->decrypt($fileContents));
    }

    public function updateStreamData(): array
    {
        $config = new Config();

        return array_map(
            static function (array $row) {
                $file = tmpfile();
                fwrite($file, $row[1]);
                fseek($file, 0);

                $row[1] = $file;

                return $row;
            },
            [
                ['update-stream-0.txt', Str::random(50), $config],
                ['update-stream-1.txt', Str::random(100), $config],
                ['update-stream-2.txt', Str::random(123), $config],
                ['update-stream-3.txt', Str::random(456), $config],
                ['update-stream-4.txt', Str::random(200), $config],
                ['update-stream-5.txt', Str::random(10), $config],
                ['update-stream-6.txt', Str::random(250), $config],
                ['update-stream-7.txt', Str::random(300), $config],
                ['update-stream-8.txt', Str::random(333), $config],
                ['update-stream-9.txt', Str::random(500), $config],
            ]
        );
    }

    /**
     * @test
     * @dataProvider readData
     *
     * @param string $path
     * @param string $contents
     */
    public function it_can_read(string $path, string $contents)
    {
        // arrange
        $adapter = $this->getDecoratedLocalAdapter('read');

        // act
        $result = $adapter->read($path);

        // assert
        $this->assertNotFalse($result);

        $this->assertSame($contents, $result['contents']);
    }

    public function readData(): array
    {
        return [
            ['read-0.txt', 'YSvdOxSZ8pyTdDWeN8qI'],
            ['read-1.txt', 'ZGW8fd0V2eimgVKWJ2Pz'],
            ['read-2.txt', '9AqgYJlgn8utDiLLzjsL'],
            ['read-3.txt', 'mNmb9MZnRMM0ezie5esf'],
            ['read-4.txt', '7SShZJqvfoWFH5D1I2cs'],
            ['read-5.txt', 'XSODwpyLu6tIJOBmWxgC'],
            ['read-6.txt', 'qLjQ53nuaK71DDzQ9jHB'],
            ['read-7.txt', 'okrz8M9h741BA5IkEEky'],
            ['read-8.txt', 'M6PXN3Ww21vcQyOVAMrS'],
            ['read-9.txt', 'Ncn0lECM2UZabcY4RQ6p'],
        ];
    }

    /**
     * @test
     * @dataProvider readStreamData
     *
     * @param string $path
     * @param string $contents
     */
    public function it_can_read_stream(string $path, string $contents)
    {
        // arrange
        $adapter = $this->getDecoratedLocalAdapter('read');

        // act
        $result = $adapter->readStream($path);

        // assert
        $this->assertNotFalse($result);
        $this->assertIsResource($result['stream']);

        $this->assertSame($contents, stream_get_contents($result['stream']));
    }

    public function readStreamData(): array
    {
        return [
            ['read-0.txt', 'YSvdOxSZ8pyTdDWeN8qI'],
            ['read-1.txt', 'ZGW8fd0V2eimgVKWJ2Pz'],
            ['read-2.txt', '9AqgYJlgn8utDiLLzjsL'],
            ['read-3.txt', 'mNmb9MZnRMM0ezie5esf'],
            ['read-4.txt', '7SShZJqvfoWFH5D1I2cs'],
            ['read-5.txt', 'XSODwpyLu6tIJOBmWxgC'],
            ['read-6.txt', 'qLjQ53nuaK71DDzQ9jHB'],
            ['read-7.txt', 'okrz8M9h741BA5IkEEky'],
            ['read-8.txt', 'M6PXN3Ww21vcQyOVAMrS'],
            ['read-9.txt', 'Ncn0lECM2UZabcY4RQ6p'],
        ];
    }

    /**
     * @test
     * @dataProvider getSizeData
     *
     * @param string $path
     * @param int    $size
     */
    public function it_can_get_size(string $path, int $size)
    {
        // arrange
        $adapter = $this->getDecoratedLocalAdapter('size');

        // act
        $result = $adapter->getSize($path);

        // assert
        $this->assertNotFalse($result);

        $this->assertSame($size, $result['size']);
    }

    public function getSizeData(): array
    {
        return [
            ['size-0.txt', 50],
            ['size-1.txt', 100],
            ['size-2.txt', 123],
            ['size-3.txt', 456],
            ['size-4.txt', 200],
            ['size-5.txt', 10],
            ['size-6.txt', 250],
            ['size-7.txt', 300],
            ['size-8.txt', 333],
            ['size-9.txt', 500],
        ];
    }

    /**
     * @test
     * @dataProvider getMimetypeData
     *
     * @param string $path
     * @param string $mimetype
     */
    public function it_can_get_mimetype(string $path, string $mimetype)
    {
        // arrange
        $adapter = $this->getDecoratedLocalAdapter('mime');

        // act
        $result = $adapter->getMimetype($path);

        // assert
        $this->assertNotFalse($result);

        $this->assertSame($mimetype, $result['mimetype']);
    }

    public function getMimetypeData(): array
    {
        return [
            ['mime-0.pdf', 'application/pdf'],
            ['mime-1.txt', 'text/plain'],
            ['mime-2.jpeg', 'image/jpeg'],
            ['mime-3.doc', 'application/msword'],
        ];
    }

    private function getDecoratedLocalAdapter(string $subDirectory = ''): EncryptedAdapter
    {
        $root = __DIR__.'/_files';

        if ($subDirectory) {
            $root .= '/'.$subDirectory;
        }

        return new EncryptedAdapter(new Local($root), $this->getEncrypter());
    }

    private function getEncrypter(): Encrypter
    {
        return new Encrypter('JwedQwbFHOZamnyxwih0Pjc029U2KQpp', 'AES-256-CBC');
    }
}
