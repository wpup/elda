<?php

namespace Acme;

class Plugin_Loader {
    public static function instance() {
        return new self;
    }
}
