<?php

declare(strict_types=1);

namespace Swis\Flysystem\Encrypted\Tests;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Str;
use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;
use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use Swis\Flysystem\Encrypted\EncryptedFilesystemAdapter;

class EncryptedFilesystemAdapterTest extends FilesystemAdapterTestCase
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
     *
     * @dataProvider writeProvider
     *
     * @param string $path
     * @param string $contents
     */
    public function writingAndEncryptingWithString(string $path, string $contents): void
    {
        // arrange
        $adapter = static::getDecoratedLocalAdapter('write');
        $encrypter = static::getEncrypter();

        // act
        $adapter->write($path, $contents, new Config());

        // assert
        $fileContents = file_get_contents(__DIR__.'/_files/write/'.$path);
        $this->assertSame($contents, $encrypter->decrypt($fileContents));
    }

    /**
     * @test
     *
     * @dataProvider writeProvider
     *
     * @param string $path
     * @param string $contents
     */
    public function writingAndEncryptingWithAStream(string $path, string $contents): void
    {
        // arrange
        $adapter = static::getDecoratedLocalAdapter('write');
        $encrypter = static::getEncrypter();

        // act
        $adapter->writeStream($path, stream_with_contents($contents), new Config());

        // assert
        $fileContents = file_get_contents(__DIR__.'/_files/write/'.$path);
        $this->assertSame($contents, $encrypter->decrypt($fileContents));
    }

    public function writeProvider(): \Generator
    {
        yield 'a file with 50 random characters' => ['write-0.txt', Str::random(50)];
        yield 'a file with 100 random characters' => ['write-1.txt', Str::random(100)];
        yield 'a file with 123 random characters' => ['write-2.txt', Str::random(123)];
        yield 'a file with 456 random characters' => ['write-3.txt', Str::random(456)];
        yield 'a file with 200 random characters' => ['write-4.txt', Str::random(200)];
        yield 'a file with 10 random characters' => ['write-5.txt', Str::random(10)];
        yield 'a file with 250 random characters' => ['write-6.txt', Str::random(250)];
        yield 'a file with 300 random characters' => ['write-7.txt', Str::random(300)];
        yield 'a file with 333 random characters' => ['write-8.txt', Str::random(333)];
        yield 'a file with 500 random characters' => ['write-9.txt', Str::random(500)];
    }

    /**
     * @test
     */
    public function writingWhenEncryptingFails(): void
    {
        // assert
        $this->expectException(UnableToWriteFile::class);

        // arrange
        $baseAdapter = $this->createMock(FilesystemAdapter::class);
        $encrypter = $this->createMock(EncrypterContract::class);
        $encrypter->expects(static::once())
            ->method('encrypt')
            ->willThrowException(new EncryptException());
        $adapter = new EncryptedFilesystemAdapter($baseAdapter, $encrypter);

        // act
        $adapter->write('path', Str::random(), new Config());
    }

    /**
     * @test
     *
     * @dataProvider readProvider
     *
     * @param string $path
     * @param string $contents
     */
    public function readingEncryptedAsString(string $path, string $contents): void
    {
        // arrange
        $adapter = static::getDecoratedLocalAdapter('read');

        // act
        $result = $adapter->read($path);

        // assert
        $this->assertSame($contents, $result);
    }

    /**
     * @test
     *
     * @dataProvider readProvider
     *
     * @param string $path
     * @param string $contents
     */
    public function readingEncryptedAsAStream(string $path, string $contents): void
    {
        // arrange
        $adapter = static::getDecoratedLocalAdapter('read');

        // act
        $result = $adapter->readStream($path);

        // assert
        $this->assertIsResource($result);
        $this->assertSame($contents, stream_get_contents($result));
    }

    public function readProvider(): \Generator
    {
        yield 'file 1' => ['read-0.txt', 'YSvdOxSZ8pyTdDWeN8qI'];
        yield 'file 2' => ['read-1.txt', 'ZGW8fd0V2eimgVKWJ2Pz'];
        yield 'file 3' => ['read-2.txt', '9AqgYJlgn8utDiLLzjsL'];
        yield 'file 4' => ['read-3.txt', 'mNmb9MZnRMM0ezie5esf'];
        yield 'file 5' => ['read-4.txt', '7SShZJqvfoWFH5D1I2cs'];
        yield 'file 6' => ['read-5.txt', 'XSODwpyLu6tIJOBmWxgC'];
        yield 'file 7' => ['read-6.txt', 'qLjQ53nuaK71DDzQ9jHB'];
        yield 'file 8' => ['read-7.txt', 'okrz8M9h741BA5IkEEky'];
        yield 'file 9' => ['read-8.txt', 'M6PXN3Ww21vcQyOVAMrS'];
        yield 'file 10' => ['read-9.txt', 'Ncn0lECM2UZabcY4RQ6p'];
    }

    /**
     * @test
     */
    public function readingWhenDecryptingFails(): void
    {
        // assert
        $this->expectException(UnableToReadFile::class);

        // arrange
        $baseAdapter = $this->createMock(FilesystemAdapter::class);
        $baseAdapter->expects(static::once())
            ->method('read')
            ->willReturn('');
        $encrypter = $this->createMock(EncrypterContract::class);
        $encrypter->expects(static::once())
            ->method('decrypt')
            ->willThrowException(new DecryptException());
        $adapter = new EncryptedFilesystemAdapter($baseAdapter, $encrypter);

        // act
        $adapter->read('path');
    }

    /**
     * @test
     *
     * @dataProvider fileSizeProvider
     *
     * @param string $path
     * @param int    $size
     */
    public function fetchingFileSizeOfEncryptedFile(string $path, int $size): void
    {
        // arrange
        $adapter = static::getDecoratedLocalAdapter('size');

        // act
        $result = $adapter->fileSize($path);

        // assert
        $this->assertSame($size, $result->fileSize());
    }

    public function fileSizeProvider(): \Generator
    {
        yield 'a file of 50 bytes' => ['size-0.txt', 50];
        yield 'a file of 100 bytes' => ['size-1.txt', 100];
        yield 'a file of 123 bytes' => ['size-2.txt', 123];
        yield 'a file of 456 bytes' => ['size-3.txt', 456];
        yield 'a file of 200 bytes' => ['size-4.txt', 200];
        yield 'a file of 10 bytes' => ['size-5.txt', 10];
        yield 'a file of 250 bytes' => ['size-6.txt', 250];
        yield 'a file of 300 bytes' => ['size-7.txt', 300];
        yield 'a file of 333 bytes' => ['size-8.txt', 333];
        yield 'a file of 500 bytes' => ['size-9.txt', 500];
    }

    /**
     * @test
     *
     * @dataProvider mimeTypeProvider
     *
     * @param string $path
     * @param string $mimetype
     */
    public function fetchingMimeTypeOfEncryptedFile(string $path, string $mimetype): void
    {
        // arrange
        $adapter = static::getDecoratedLocalAdapter('mime');

        // act
        $result = $adapter->mimeType($path);

        // assert
        $this->assertSame($mimetype, $result->mimeType());
    }

    public function mimeTypeProvider(): \Generator
    {
        yield 'a pdf file' => ['mime-0.pdf', 'application/pdf'];
        yield 'a txt file' => ['mime-1.txt', 'text/plain'];
        yield 'a jpeg file' => ['mime-2.jpeg', 'image/jpeg'];
        yield 'a doc file' => ['mime-3.doc', 'application/msword'];
    }

    /**
     * @test
     */
    public function forwardingCallsToDecoratedAdapter(): void
    {
        // arrange
        $baseAdapter = $this->createMock(InMemoryFilesystemAdapter::class);
        $baseAdapter->expects(static::once())
            ->method('deleteEverything');
        $adapter = new EncryptedFilesystemAdapter($baseAdapter, static::getEncrypter());

        // act
        $adapter->deleteEverything();

        // assert using expectations
    }

    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        return static::getDecoratedLocalAdapter('integration');
    }

    private static function getDecoratedLocalAdapter(string $subDirectory = ''): EncryptedFilesystemAdapter
    {
        $root = __DIR__.'/_files';

        if ($subDirectory) {
            $root .= '/'.$subDirectory;
        }

        return new EncryptedFilesystemAdapter(new LocalFilesystemAdapter($root), static::getEncrypter());
    }

    private static function getEncrypter(): Encrypter
    {
        return new Encrypter('JwedQwbFHOZamnyxwih0Pjc029U2KQpp', 'AES-256-CBC');
    }
}
