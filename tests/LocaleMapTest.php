<?php
use PHPUnit\Framework\TestCase;
use Wemdo\AIAgent\Plugin;

class LocaleMapTest extends TestCase {
    public static function setUpBeforeClass(): void {
        // Pure utility — no WP environment needed for the locale mapper.
        // ABSPATH guard is the only WP symbol the class touches at load
        // time, and we satisfy it with a stub define so the require below
        // doesn't bail at the early-exit check.
        if (!defined('ABSPATH')) define('ABSPATH', '/tmp/');
        require_once __DIR__ . '/../src/Plugin.php';
    }

    public function test_hr_locale_maps_to_hr() {
        $this->assertSame('hr', Plugin::map_locale('hr_HR'));
    }

    public function test_en_locale_maps_to_en() {
        $this->assertSame('en', Plugin::map_locale('en_US'));
        $this->assertSame('en', Plugin::map_locale('en_GB'));
    }

    public function test_unknown_falls_back_to_en() {
        $this->assertSame('en', Plugin::map_locale('xx_YY'));
        $this->assertSame('en', Plugin::map_locale(''));
    }

    public function test_de_pt_es_pl_recognized() {
        $this->assertSame('de', Plugin::map_locale('de_DE'));
        $this->assertSame('pt', Plugin::map_locale('pt_BR'));
        $this->assertSame('es', Plugin::map_locale('es_ES'));
        $this->assertSame('pl', Plugin::map_locale('pl_PL'));
    }
}
