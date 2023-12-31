<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    protected string $url;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->url = '/api/v1/recordings';
    }
}
