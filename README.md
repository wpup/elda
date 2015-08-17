# elda

> WIP

WordPress Customer Plugin Bootstrapper. Elda loades files and register the [wp-autoload](https://github.com/frozzare/wp-autoload).

## Example

```php
/**
 * Bootstrap Customer plugin with Elda.
 */
Elda::boot( __DIR__, [
    'files'     => [
      'lib/papi.php'
    ],
    'namespace' => 'Customer\\'
] );
```

## License

MIT © [Fredrik Forsmo](https://github.com/frozzare)
