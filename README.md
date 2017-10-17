# elda

[![Build Status](https://travis-ci.org/wpup/elda.svg?branch=master)](https://travis-ci.org/wpup/elda)  [![codecov.io](http://codecov.io/github/wpup/elda/coverage.svg?branch=master)](http://codecov.io/github/wpup/elda?branch=master)

Simple WordPress Plugin Bootstrapper. Elda loades files and register the [wp-autoload](https://github.com/wpup/autoload). Default Elda looks for code in `src` directory in your plugin directory, this can be changed with `src_dir` in options array.

## Install

```
composer require frozzare/elda
```

## Example

```php
/**
 * Plugin Name: Customer
 * Description: Customer description
 * Author: Customer
 * Author URI: http://example.com
 * Version: 1.0.0
 * Textdomain: customer
 */

use Frozzare\Elda\Elda;

/**
 * Bootstrap Customer plugin with Elda.
 */
Elda::boot( __FILE__, [
  'domain'    => 'customer',
  'files'     => [
    'lib/papi.php'
  ],
  'namespace' => 'Customer\\'
] );
```

## License

MIT © [Fredrik Forsmo](https://github.com/frozzare)
