<?php

namespace Frozzare\Tests\Elda;

use Frozzare\Elda\Elda;

class EldaTest extends \WP_UnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function test_basic() {
        Elda::boot( __DIR__ . '/fixtures/acme/acme-1.php', [
            'namespace' => 'Acme\\',
            'src_dir'   => ''
        ] );

        do_action( 'plugins_loaded' );

        $this->assertTrue( \Acme\Plugin_Loader::instance() instanceof \Acme\Plugin_Loader );
    }

    public function test_with_instance() {
        Elda::boot( __DIR__ . '/fixtures/acme/acme-2.php', [
            'instance' => 'Acme\\Plugin_Loader::instance',
            'src_dir'  => ''
        ] );

        do_action( 'plugins_loaded' );

        $this->assertEquals( Elda::make( __DIR__ . '/fixtures/acme/acme-2.php' ), \Acme\Plugin_Loader::instance() );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage `Acme\Plugin_Loader::instance2` is not callable
     */
    public function test_with_instance_exception() {
        Elda::boot( __DIR__ . '/fixtures/acme/acme-2-1.php', [
            'instance' => 'Acme\\Plugin_Loader::instance2',
            'src_dir'  => ''
        ] );

        do_action( 'plugins_loaded' );
    }

    public function test_load_textdomain() {
        global $l10n;

        Elda::boot( __DIR__ . '/fixtures/acme/acme-3.php' );

        $this->assertNotEmpty( $l10n );

        Elda::boot( __DIR__ . '/fixtures/acme/acme-3-1.php', [
            'domain' => 'acme'
        ] );

        $this->assertNotEmpty( $l10n );

        Elda::boot( __DIR__ . '/fixtures/acme/acme-3-2.php', [
            'domain'  => 'acme',
            'src_dir' => ''
        ] );

        do_action( 'plugins_loaded' );

        $this->assertTrue( isset( $l10n['acme'] ) );
    }

    public function test_load_files() {
        Elda::boot( __DIR__ . '/fixtures/acme/acme-4.php', [
            'files'    => [
                'lib/test.php'
            ],
            'instance' => 'Acme\\Plugin_Loader::instance',
            'src_dir'  => ''
        ] );

        do_action( 'plugins_loaded' );

        $this->assertEquals( 'acme', acme_test() );
    }

    public function test_make() {
        Elda::boot( __DIR__ . '/fixtures/acme/acme-5.php', [
            'namespace' => 'Acme\\',
            'src_dir'   => ''
        ] );

        do_action( 'plugins_loaded' );

        $this->assertTrue( Elda::make( __DIR__ . '/fixtures/acme/acme-5.php' ) instanceof Elda );

        Elda::boot( __DIR__ . '/fixtures/acme/acme-6.php', [
            'instance' => 'Acme\\Plugin_Loader::instance',
            'src_dir'  => ''
        ] );

        do_action( 'plugins_loaded' );

        $this->assertEquals( Elda::make( __DIR__ . '/fixtures/acme/acme-6.php' ), \Acme\Plugin_Loader::instance() );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Cannot boot `wp-elda/tests/fixtures/acme/acme-6.php` again
     */
    public function test_boot_exception() {
        Elda::boot( __DIR__ . '/fixtures/acme/acme-6.php', [
            'instance' => 'Acme\\Plugin_Loader::instance',
            'src_dir'  => ''
        ] );

        do_action( 'plugins_loaded' );
    }

    public function test_make_exception() {
        try {
            Elda::make( __FILE__ );
        } catch ( \Exception $e ) {
            $this->assertEquals( sprintf( 'Identifier `%s` is not defined', plugin_basename( __FILE__ ) ), $e->getMessage() );
        }
    }

    public function test_base_path_exception() {
        try {
            Elda::boot( false );
        } catch ( \InvalidArgumentException $e ) {
            $this->assertEquals( 'Invalid argument. `$base_path` must be string.', $e->getMessage() );
        }
    }

    public function test_options_exceptions() {
        try {
            Elda::boot( __DIR__, [
                'instance' => false
            ] );
        } catch ( \InvalidArgumentException $e ) {
            $this->assertEquals( 'Invalid argument. `instance` must be string.', $e->getMessage() );
        }

        try {
            Elda::boot( __DIR__, [
                'domain' => false
            ] );
        } catch ( \InvalidArgumentException $e ) {
            $this->assertEquals( 'Invalid argument. `domain` must be string.', $e->getMessage() );
        }

        try {
            Elda::boot( __DIR__, [
                'lang_dir' => false
            ] );
        } catch ( \InvalidArgumentException $e ) {
            $this->assertEquals( 'Invalid argument. `lang_dir` must be string.', $e->getMessage() );
        }

        try {
            Elda::boot( __DIR__, [
                'namespace' => false
            ] );
        } catch ( \InvalidArgumentException $e ) {
            $this->assertEquals( 'Invalid argument. `namespace` must be string.', $e->getMessage() );
        }

        try {
            Elda::boot( __DIR__, [
                'src_dir' => false
            ] );
        } catch ( \InvalidArgumentException $e ) {
            $this->assertEquals( 'Invalid argument. `src_dir` must be string.', $e->getMessage() );
        }

        try {
            Elda::boot( __DIR__, [
                'files' => false
            ] );
        } catch ( \InvalidArgumentException $e ) {
            $this->assertEquals( 'Invalid argument. `files` must be array.', $e->getMessage() );
        }
    }
}
