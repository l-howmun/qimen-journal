<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Load a verified Qi Men reference chart fixture.
 *
 * Fixtures are transcribed from reference software hour charts and
 * cross-checked against kinqimen. They are the specification, not test scaffolding:
 * if the engine disagrees with a fixture, the engine is wrong.
 *
 * @return array<string, mixed>
 */
function qimenFixture(string $name): array
{
    $path = __DIR__.'/Fixtures/qimen/'.$name.'.json';

    if (! is_file($path)) {
        throw new RuntimeException("Missing Qi Men fixture: {$path}");
    }

    return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
}

/**
 * Reduce a list of backed enums to their raw values, for comparison against fixtures.
 *
 * @param  array<int, BackedEnum>  $cases
 * @return array<int, string>
 */
function enumValues(array $cases): array
{
    return array_map(fn (BackedEnum $case): string => (string) $case->value, $cases);
}
