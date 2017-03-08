<?php

namespace base\filter;

use stdClass;

interface Filter {
    public function inputFilter($data, stdClass $context);
    public function outputFilter($data, stdClass $context);
}
