# Flysystem Encrypted Filesystem Adapter

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]
[![Made by SWIS][ico-swis]][link-swis]

This Flysystem adapter is a transparent decorator that encrypts/decrypts files using the [Illuminate Encrypter](https://packagist.org/packages/illuminate/encryption).

## Install

Via Composer

``` bash
$ composer require swisnl/flysystem-encrypted
```

N.B. Using Flysystem 1? Please use version [1.x](https://github.com/swisnl/flysystem-encrypted/tree/1.x) of this adapter.

## Usage

``` php
use Illuminate\Encryption\Encrypter;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Swis\Flysystem\Encrypted\EncryptedFilesystemAdapter;

// Create the adapter
$localAdapter = new Local('/path/to/root');

// Create the encrypter
$encrypter = new Encrypter('key', 'cipher');

// Decorate the adapter
$adapter = new EncryptedFilesystemAdapter($localAdapter, $encrypter);

// And use that to create the file system
$filesystem = new Filesystem($adapter);
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email security@swis.nl instead of using the issue tracker.

## Credits

- [Jasper Zonneveld][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## SWIS :heart: Open Source

[SWIS][link-swis] is a web agency from Leiden, the Netherlands. We love working with open source software. 

[ico-version]: https://img.shields.io/packagist/v/swisnl/flysystem-encrypted.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/swisnl/flysystem-encrypted/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/swisnl/flysystem-encrypted.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/swisnl/flysystem-encrypted.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/swisnl/flysystem-encrypted.svg?style=flat-square
[ico-swis]: https://img.shields.io/badge/%F0%9F%9A%80-made%20by%20SWIS-%23D9021B.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/swisnl/flysystem-encrypted
[link-travis]: https://travis-ci.org/swisnl/flysystem-encrypted
[link-scrutinizer]: https://scrutinizer-ci.com/g/swisnl/flysystem-encrypted/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/swisnl/flysystem-encrypted
[link-downloads]: https://packagist.org/packages/swisnl/flysystem-encrypted
[link-author]: https://github.com/swisnl
[link-contributors]: ../../contributors
[link-swis]: https://www.swis.nl
