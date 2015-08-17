<?php

namespace Frozzare\Tests\Elda;

use Frozzare\Elda\Elda;

class EldaTest extends \WP_UnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function test_basic() {
        $elda = Elda::boot( __DIR__ . '/fixtures/acme/acme-1.php', [
            'namespace' => 'Acme\\',
            'src_dir'   => ''
        ] );

        $this->assertTrue( \Acme\Plugin_Loader::instance() instanceof \Acme\Plugin_Loader );
        $this->assertTrue( $elda->get_instance() instanceof Elda );
    }

    public function test_with_instance() {
        $acme = Elda::boot( __DIR__ . '/fixtures/acme/acme-2.php', [
            'instance' => 'Acme\\Plugin_Loader::instance',
            'src_dir'  => ''
        ] );

        $this->assertEquals( \Acme\Plugin_Loader::instance(), $acme );
    }

    public function test_load_files() {
        Elda::boot( __DIR__ . '/fixtures/acme/acme-3.php', [
            'files'    => [
                'lib/test.php'
            ],
            'instance' => 'Acme\\Plugin_Loader::instance',
            'src_dir'  => ''
        ] );

        $this->assertEquals( 'acme', acme_test() );
    }

    public function test_make() {
        Elda::boot( __DIR__ . '/fixtures/acme/acme-4.php', [
            'namespace' => 'Acme\\',
            'src_dir'   => ''
        ] );

        $this->assertTrue( Elda::make( __DIR__ . '/fixtures/acme/acme-4.php' ) instanceof Elda );

        $acme = Elda::boot( __DIR__ . '/fixtures/acme/acme-5.php', [
            'instance' => 'Acme\\Plugin_Loader::instance',
            'src_dir'  => ''
        ] );

        $this->assertEquals( \Acme\Plugin_Loader::instance(), $acme );
        $this->assertEquals( \Acme\Plugin_Loader::instance(), Elda::make( __DIR__ . '/fixtures/acme/acme-5.php' ) );
    }

}
