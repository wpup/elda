<?php

namespace Frozzare\Elda;

class Elda {

    /**
     * Base path for the plugin.
     *
     * @var string
     */
    protected $base_path;

    /**
     * Elda options.
     *
     * @var array
     */
     protected $options = [
        'files'     => [],
        'namespace' => '',
        'src_dir'   => 'src'
    ];

    /**
     * The constructor.
     *
     * @param string $base_path
     * @param array $options
     */
    protected function __construct( $base_path, array $options = [] ) {
        $this->base_path = $base_path;
        $this->options   = array_merge( $this->options, $options );
        $this->options   = (object) $this->options;

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
        add_action( 'plugins_loaded', function () {
            return new self( $base_path, $options );
        } );
    }

    /**
     * Add files that should be loaded.
     *
     * @param array $files
     */
    public function files( array $files ) {
        $this->options->files = array_filter( $files, function ( $file ) {
            return file_exists( $this->get_src_path( $file ) );
        } );
    }

    /**
     * Get full path to the path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function get_path( $path ) {
        return rtrim( $this->base_path, '/' ) . '/' . $path;
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

}
