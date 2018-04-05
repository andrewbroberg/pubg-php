# pubg-php

[![Latest Version on Packagist][ico-version]][link-packagist]
<!-- [![Software License][ico-license]](LICENSE.md) -->
<!-- [![Build Status][ico-travis]][link-travis] -->
<!-- [![Coverage Status][ico-scrutinizer]][link-scrutinizer] -->
<!-- [![Quality Score][ico-code-quality]][link-code-quality] -->
[![Total Downloads][ico-downloads]][link-downloads]

PUBG API PHP Wrapper

## Structure

If any of the following are applicable to your project, then the directory structure should follow industry best practices by being named the following.

```
bin/        
config/
src/
tests/
vendor/
```


## Install

Via Composer

``` bash
$ composer require andrewbroberg/pubg-php
```

## Usage

``` php
$api = new AndrewBroberg\PUBG\API();
$api->getMatch('pc-oc', 'matchid');
$api->getPlayer('pc-oc', 'playerid');
$api->getPlayers('pc-oc', ['playerIds' => [
    'playerid',
]]);
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

If you discover any security related issues, please email andrew.broberg@gmail.com instead of using the issue tracker.

## Credits

- [Andrew Broberg][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/andrewbroberg/pubg-php.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/andrewbroberg/pubg-php/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/andrewbroberg/pubg-php.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/andrewbroberg/pubg-php.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/andrewbroberg/pubg-php.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/andrewbroberg/pubg-php
[link-travis]: https://travis-ci.org/andrewbroberg/pubg-php
[link-scrutinizer]: https://scrutinizer-ci.com/g/andrewbroberg/pubg-php/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/andrewbroberg/pubg-php
[link-downloads]: https://packagist.org/packages/andrewbroberg/pubg-php
[link-author]: https://github.com/andrewbroberg
[link-contributors]: ../../contributors
