<?php

declare(strict_types=1);

namespace Swis\Flysystem\Encrypted;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Support\Traits\ForwardsCalls;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Config;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToWriteFile;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;
use League\MimeTypeDetection\MimeTypeDetector;

class EncryptedFilesystemAdapter implements FilesystemAdapter
{
    use ForwardsCalls;

    /**
     * @var \League\Flysystem\FilesystemAdapter
     */
    private $adapter;

    /**
     * @var \Illuminate\Contracts\Encryption\Encrypter
     */
    private $encrypter;

    /**
     * @var \League\MimeTypeDetection\MimeTypeDetector
     */
    private $mimeTypeDetector;

    /**
     * @param \League\Flysystem\FilesystemAdapter $adapter
     * @param \Illuminate\Contracts\Encryption\Encrypter $encrypter
     * @param \League\MimeTypeDetection\MimeTypeDetector|null $mimeTypeDetector
     */
    public function __construct(FilesystemAdapter $adapter, Encrypter $encrypter, MimeTypeDetector $mimeTypeDetector = null)
    {
        $this->adapter = $adapter;
        $this->encrypter = $encrypter;
        $this->mimeTypeDetector = $mimeTypeDetector ?: new ExtensionMimeTypeDetector();
    }

    /**
     * @inheritDoc
     */
    public function fileExists(string $path): bool
    {
        return $this->adapter->fileExists($path);
    }

    /**
     * @inheritDoc
     */
    public function write(string $path, string $contents, Config $config): void
    {
        try {
            $this->adapter->write($path, $this->encrypter->encrypt($contents), $config);
        } catch (EncryptException $exception) {
            throw UnableToWriteFile::atLocation($path, $exception->getMessage(), $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->write($path, stream_get_contents($contents), $config);
    }

    /**
     * @inheritDoc
     */
    public function read(string $path): string
    {
        try {
            return $this->encrypter->decrypt($this->adapter->read($path));
        } catch (DecryptException $exception) {
            throw UnableToReadFile::fromLocation($path, $exception->getMessage(), $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function readStream(string $path)
    {
        $stream = tmpfile();

        if ($stream === false) {
            throw UnableToReadFile::fromLocation($path, 'Unable to create temporary stream.');
        }

        fwrite($stream, $this->read($path));
        fseek($stream, 0);

        return $stream;
    }

    /**
     * @inheritDoc
     */
    public function delete(string $path): void
    {
        $this->adapter->delete($path);
    }

    /**
     * @inheritDoc
     */
    public function deleteDirectory(string $path): void
    {
        $this->adapter->deleteDirectory($path);
    }

    /**
     * @inheritDoc
     */
    public function createDirectory(string $path, Config $config): void
    {
        $this->adapter->createDirectory($path, $config);
    }

    /**
     * @inheritDoc
     */
    public function setVisibility(string $path, string $visibility): void
    {
        $this->adapter->setVisibility($path, $visibility);
    }

    /**
     * @inheritDoc
     */
    public function visibility(string $path): FileAttributes
    {
        return $this->adapter->visibility($path);
    }

    /**
     * @inheritDoc
     */
    public function mimeType(string $path): FileAttributes
    {
        try {
            $mimeType = $this->mimeTypeDetector->detectMimeType($path, $this->read($path));
        } catch (UnableToReadFile $exception) {
            throw UnableToRetrieveMetadata::mimeType($path, $exception->getMessage(), $exception);
        }

        if ($mimeType === null) {
            throw UnableToRetrieveMetadata::mimeType($path);
        }

        return new FileAttributes($path, null, null, null, $mimeType);
    }

    /**
     * @inheritDoc
     */
    public function lastModified(string $path): FileAttributes
    {
        return $this->adapter->lastModified($path);
    }

    /**
     * @inheritDoc
     */
    public function fileSize(string $path): FileAttributes
    {
        try {
            return new FileAttributes($path, \strlen($this->read($path)));
        } catch (UnableToReadFile $exception) {
            throw UnableToRetrieveMetadata::fileSize($path, $exception->getMessage(), $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function listContents(string $path, bool $deep): iterable
    {
        return $this->adapter->listContents($path, $deep);
    }

    /**
     * @inheritDoc
     */
    public function move(string $source, string $destination, Config $config): void
    {
        $this->adapter->move($source, $destination, $config);
    }

    /**
     * @inheritDoc
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        $this->adapter->copy($source, $destination, $config);
    }

    /**
     * Dynamically pass missing methods to the decorated adapter.
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->forwardCallTo($this->adapter, $method, $parameters);
    }
}
