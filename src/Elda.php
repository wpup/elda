<?php

namespace Frozzare\Elda;

use Exception;
use InvalidArgumentException;

class Elda {

    /**
     * Base path for the plugin.
     *
     * @var string
     */
    protected $base_path;

    /**
     * Instances of booted plugins.
     *
     * @var array
     */
    protected static $instances = [];

    /**
     * Elda options.
     *
     * @var array
     */
     protected $options = [
        'files'     => [],
        'instance'  => null,
        'namespace' => '',
        'src_dir'   => 'src'
    ];

    /**
     * The plugin name.
     *
     * @var string
     */
    protected $plugin_name;

    /**
     * The constructor.
     *
     * @param string $base_path
     * @param array $options
     */
    protected function __construct( $base_path, array $options = [] ) {
        $this->set_base_path( $base_path );
        $this->set_options( $options );
        $this->load_composer();
        $this->register_autoload();
        $this->load_files();
    }

    /**
     * Boot the plugin.
     *
     * @param string $base_path
     * @param array $options
     *
     * @return \Frozzare\Elda\Elda
     */
    public static function boot( $base_path, array $options = [] ) {
        $name = plugin_basename( $base_path );

        if ( ! isset( self::$instances[$name] ) ) {
            self::$instances[$name] = ( new self( $base_path, $options ) )->get_instance();
        }

        $instance = self::$instances[$name];

        add_action( 'plugins_loaded', function () use( $instance ) {
            return $instance;
        } );

        return $instance;
    }

    /**
     * Add files that should be loaded.
     *
     * @param array $files
     */
    public function files( array $files ) {
        $this->options->files = array_filter( $files, function ( $file ) {
            return is_string( $file ) && file_exists( $this->get_src_path( $file ) );
        } );
    }

    /**
     * Get instance.
     *
     * @return object
     */
    public function get_instance() {
        if ( is_null( $this->options->instance ) ) {
            return $this;
        }

        if ( ! is_callable( $this->options->instance ) ) {
            throw new Exception( sprintf( '`%s` is not callable', $this->options->instance ) );
        }

        return call_user_func( $this->options->instance );
    }

    /**
     * Get full path to the path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function get_path( $path ) {
        return $this->base_path . '/' . $path;
    }

    /**
     * Get full src path to the path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function get_src_path( $path = '' ) {
        return rtrim( $this->get_path( $this->options->src_dir ), '/' ) .
            ( strlen( $path )  ? '/' . $path : '' );
    }

    /**
     * Load Composer autoload if it exists.
     */
    protected function load_composer() {
        $path = $this->get_path( 'vendor/autoload.php' );

        if ( file_exists( $path ) ) {
            require_once $path;
        }
    }

    /**
     * Load files.
     */
    protected function load_files() {
        foreach ( $this->options->files as $file ) {
            require_once $this->get_src_path( $file );
        }

        unset( $file );
    }

    /**
     * Register autoload.
     */
    protected function register_autoload() {
        $src_dir = $this->get_src_path();

        if ( file_exists( $src_dir ) ) {
            register_wp_autoload( $this->options->namespace, $src_dir );
        }
    }

    /**
     * Set base path. Remove file name if it exists.
     *
     * @param string $base_path
     */
    protected function set_base_path( $base_path ) {
        if ( ! is_string( $base_path ) ) {
            throw new InvalidArgumentException( 'Invalid argument. `$base_path` must be string.' );
        }

        if ( strpos( $base_path, '.php' ) !== false ) {
            $base_path = preg_replace( '/[^\/]*$/', '', $base_path );
        }

        $this->base_path = rtrim( $base_path, '/' );
    }

    /**
     * Set namespace if instance exists and namespace is empty.
     */
    protected function set_namespace() {
        if ( empty( $this->options->instance ) || ! empty( $this->options->namespace ) ) {
            return;
        }

        $namespace = $this->options->instance;
        $namespace = explode( '\\', $namespace );
        array_pop( $namespace );

        $this->options->namespace = implode( '\\', $namespace );
    }

    /**
     * Set options.
     *
     * @param array $options
     */
    protected function set_options( array $options ) {
        $this->options = array_merge( $this->options, $options );
        $this->options = (object) $this->options;

        if ( ! is_string( $this->options->instance ) ) {
            throw new InvalidArgumentException( 'Invalid argument. `instance` must be string.' );
        }

        $this->set_namespace();

        if ( ! is_string( $this->options->namespace ) ) {
            throw new InvalidArgumentException( 'Invalid argument. `namespace` must be string.' );
        }

        if ( ! is_string( $this->options->src_dir ) ) {
            throw new InvalidArgumentException( 'Invalid argument. `src_dir` must be string.' );
        }

        if ( ! is_array( $this->options->files ) ) {
            throw new InvalidArgumentException( 'Invalid argument. `files` must be array.' );
        }
    }

}
