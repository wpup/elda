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
        'action'    => 'plugins_loaded',
        'domain'    => '',
        'files'     => [],
        'instance'  => '',
        'lang_dir'  => 'languages',
        'lang_path' => '',
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
     * @param array  $options
     */
    protected function __construct( $base_path, array $options = [] ) {
        $this->set_base_path( $base_path );
        $this->set_options( $options );
        $this->load_textdomain();
        $this->load_composer();
        $this->register_autoload();
    }

    /**
     * Boot the plugin.
     *
     * @param  string $base_path
     * @param  array  $options
     *
     * @throws Exception if plugin is loaded
     */
    public static function boot( $base_path, array $options = [] ) {
        $name = plugin_basename( $base_path );

        if ( isset( static::$instances[$name] ) ) {
            throw new Exception( sprintf( 'Cannot boot `%s` again', $name ) );
        }

        $instance = new static( $base_path, $options );

        // @codeCoverageIgnoreStart
        add_action( $instance->get_action(), function () use ( $instance, $name ) {
            return static::$instances[$name] = $instance->load_files()->get_instance();
        } );
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get action.
     *
     * @return string
     */
    public function get_action() {
        return $this->options->action;
    }

    /**
     * Get instance.
     *
     * @return object
     */
    public function get_instance() {
        if ( empty( $this->options->instance ) ) {
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
     * @param  string $path [description]
     *
     * @return string
     */
    protected function get_path( $path ) {
        return $this->base_path . '/' . $path;
    }

    /**
     * Get full src path to the path.
     *
     * @param  string $path
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

        // @codeCoverageIgnoreStart
        if ( file_exists( $path ) ) {
            require_once $path;
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Load files.
     */
    protected function load_files() {
        foreach ( $this->options->files as $file ) {
            require_once $this->get_src_path( $file );
        }

        unset( $file );

        return $this;
    }

    /**
     * Load textdomain.
     */
    protected function load_textdomain() {
        $domain = $this->options->domain;

        if ( empty( $domain ) ) {
            return;
        }

        $path = $this->options->lang_path;

        if ( empty( $path ) ) {
            $path = $this->get_path( $this->options->lang_dir );
        }

        $path = sprintf( '%s/%s-%s.mo', rtrim( $path, '/' ), $domain, get_locale() );

        load_textdomain( $domain, $path );
    }

    /**
     * Get the instance from base path.
     *
     * @param  string $base_path
     *
     * @throws InvalidArgumentException when plugin instance don't exists.
     *
     * @return object
     */
    public static function make( $base_path ) {
        $name = plugin_basename( $base_path );

        if ( ! isset( self::$instances[$name] ) ) {
            throw new InvalidArgumentException( sprintf( 'Identifier `%s` is not defined', $name ) );
        }

        return self::$instances[$name];
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

        $namespace = $this->options->namespace;
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

        if ( ! is_string( $this->options->action ) ) {
            throw new InvalidArgumentException( 'Invalid argument. `action` must be string.' );
        }

        if ( ! is_string( $this->options->domain ) ) {
            throw new InvalidArgumentException( 'Invalid argument. `domain` must be string.' );
        }

        if ( ! is_string( $this->options->lang_dir ) ) {
            throw new InvalidArgumentException( 'Invalid argument. `lang_dir` must be string.' );
        }

        if ( ! is_string( $this->options->lang_path ) ) {
            throw new InvalidArgumentException( 'Invalid argument. `lang_path` must be string.' );
        }

        if ( ! is_string( $this->options->namespace ) ) {
            throw new InvalidArgumentException( 'Invalid argument. `namespace` must be string.' );
        }

        if ( ! is_string( $this->options->src_dir ) ) {
            throw new InvalidArgumentException( 'Invalid argument. `src_dir` must be string.' );
        }

        if ( ! is_array( $this->options->files ) ) {
            throw new InvalidArgumentException( 'Invalid argument. `files` must be array.' );
        }

        $this->options->files = array_filter( $this->options->files, function ( $file ) {
            return is_string( $file ) && file_exists( $this->get_src_path( $file ) );
        } );
    }
}
