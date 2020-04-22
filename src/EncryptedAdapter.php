<?php

declare(strict_types=1);

namespace Swis\Flysystem\Encrypted;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Traits\ForwardsCalls;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util\MimeType;

class EncryptedAdapter implements AdapterInterface
{
    use ForwardsCalls;

    /**
     * @var \League\Flysystem\AdapterInterface
     */
    private $adapter;

    /**
     * @var \Illuminate\Contracts\Encryption\Encrypter
     */
    private $encrypter;

    /**
     * @param \League\Flysystem\AdapterInterface $adapter
     * @param \Illuminate\Contracts\Encryption\Encrypter $encrypter
     */
    public function __construct(AdapterInterface $adapter, Encrypter $encrypter)
    {
        $this->adapter = $adapter;
        $this->encrypter = $encrypter;
    }

    /**
     * @inheritDoc
     */
    public function write($path, $contents, Config $config)
    {
        return $this->adapter->write($path, $this->encrypter->encrypt($contents), $config);
    }

    /**
     * @inheritDoc
     */
    public function writeStream($path, $resource, Config $config)
    {
        return $this->write($path, stream_get_contents($resource), $config);
    }

    /**
     * @inheritDoc
     */
    public function update($path, $contents, Config $config)
    {
        return $this->adapter->update($path, $this->encrypter->encrypt($contents), $config);
    }

    /**
     * @inheritDoc
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->update($path, stream_get_contents($resource), $config);
    }

    /**
     * @inheritDoc
     */
    public function rename($path, $newpath)
    {
        return $this->adapter->rename($path, $newpath);
    }

    /**
     * @inheritDoc
     */
    public function copy($path, $newpath)
    {
        return $this->adapter->copy($path, $newpath);
    }

    /**
     * @inheritDoc
     */
    public function delete($path)
    {
        return $this->adapter->delete($path);
    }

    /**
     * @inheritDoc
     */
    public function deleteDir($dirname)
    {
        return $this->adapter->deleteDir($dirname);
    }

    /**
     * @inheritDoc
     */
    public function createDir($dirname, Config $config)
    {
        return $this->adapter->createDir($dirname, $config);
    }

    /**
     * @inheritDoc
     */
    public function setVisibility($path, $visibility)
    {
        return $this->adapter->setVisibility($path, $visibility);
    }

    /**
     * @inheritDoc
     */
    public function has($path)
    {
        return $this->adapter->has($path);
    }

    /**
     * @inheritDoc
     */
    public function read($path)
    {
        $result = $this->adapter->read($path);

        if ($result === false) {
            return false;
        }

        return array_merge($result, ['contents' => $this->encrypter->decrypt($result['contents'])]);
    }

    /**
     * @inheritDoc
     */
    public function readStream($path)
    {
        $result = $this->read($path);

        if ($result === false) {
            return false;
        }

        $stream = tmpfile();
        fwrite($stream, $result['contents']);
        fseek($stream, 0);
        unset($result['contents']);

        return array_merge($result, compact('stream'));
    }

    /**
     * @inheritDoc
     */
    public function listContents($directory = '', $recursive = false)
    {
        return $this->adapter->listContents($directory, $recursive);
    }

    /**
     * @inheritDoc
     */
    public function getMetadata($path)
    {
        return $this->adapter->getMetadata($path);
    }

    /**
     * @inheritDoc
     */
    public function getSize($path)
    {
        $result = $this->adapter->getSize($path);
        $contents = $this->read($path);

        if ($result === false || $contents === false) {
            return false;
        }

        $result['size'] = \strlen($contents['contents']);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getMimetype($path)
    {
        $result = $this->adapter->getMimetype($path);

        if ($result === false) {
            return false;
        }

        $result['mimetype'] = MimeType::detectByFilename($path);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getTimestamp($path)
    {
        return $this->adapter->getTimestamp($path);
    }

    /**
     * @inheritDoc
     */
    public function getVisibility($path)
    {
        return $this->adapter->getVisibility($path);
    }

    /**
     * Dynamically pass missing methods to the decorated adapter.
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->adapter, $method, $parameters);
    }
}
