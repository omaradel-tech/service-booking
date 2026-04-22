<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The TestCase you're using is the base class for all tests. It's
| responsible for creating the application and handling the test
| environment.
|
*/

uses(
    Tests\TestCase::class,
    RefreshDatabase::class,
)->in('Feature');

uses(
    Tests\TestCase::class,
)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| Here you may define expectations that will be used throughout the
| test suite. These expectations are great for giving your tests
| more expressive and readable assertions.
|
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out of the box, you may have some
| testing code specific to your project that you don't want to
| repeat in every test. Here you can also define helper functions
| that will be available in all your tests.
|
*/

function something()
{
    // ..
}
