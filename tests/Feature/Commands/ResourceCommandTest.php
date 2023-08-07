<?php

use MoonShine\Commands\MakeResourceCommand;
use MoonShine\MoonShine;
use Symfony\Component\Console\Command\Command;

use function Pest\Laravel\artisan;

uses()->group('commands');

it('reports progress', function (): void {
    artisan(MakeResourceCommand::class)
        ->expectsQuestion('Name', 'Test')
        ->expectsOutputToContain('Now register resource in menu')
        ->assertExitCode(Command::SUCCESS);
});

it('reports progress singleton', function (): void {
    artisan(MakeResourceCommand::class, ['--singleton' => true])
        ->expectsQuestion('Name', 'Test')
        ->expectsQuestion('Item id', 1)
        ->expectsOutputToContain('Now register resource in menu')
        ->assertExitCode(Command::SUCCESS);
});

it('generates correct resource title', function (
    string $result,
    string $name,
    bool $singleton,
    int $id = null,
    string $title = null,
): void {
    artisan(MakeResourceCommand::class, [
        'name' => $name,
        '--title' => $title,
        '--singleton' => $singleton,
        '--id' => $id,
    ])->assertExitCode(Command::SUCCESS);

    $path = MoonShine::path('app/MoonShine/Resources/' . ucfirst($name) . 'Resource.php');
    $contents = file_get_contents($path);

    expect($contents)
        ->toContain('public static string $title = \'' . $result . '\';');

    @unlink($path);
})->with([
    'singular resource' => [
        'Children', // result
        'Child', // resource name
        false, // is singleton
        null, // id of singleton resource
        null, // title
    ],
    'singular singleton resource' => [
        'Child', // result
        'Child', // resource name
        true, // is singleton
        1, // id of singleton resource
        null, // title
    ],
    'singular resource with title' => [
        'Boys', // result
        'Child', // resource name
        false, // is singleton
        null, // id of singleton resource
        'Boys', // title
    ],
    'singular singleton resource with title' => [
        'Boy', // result
        'Child', // resource name
        true, // is singleton
        1, // id of singleton resource
        'Boy', // title
    ],

    'plural resource' => [
        'Children', // result
        'Children', // resource name
        false, // is singleton
        null, // id of singleton resource
        null, // title
    ],
    'plural singleton resource' => [
        'Children', // result
        'Children', // resource name
        true, // is singleton
        1, // id of singleton resource
        null, // title
    ],
    'plural resource with title' => [
        'Boys', // result
        'Children', // resource name
        false, // is singleton
        null, // id of singleton resource
        'Boys', // title
    ],
    'plural singleton resource with title' => [
        'Boy', // result
        'Children', // resource name
        true, // is singleton
        1, // id of singleton resource
        'Boy', // title
    ],
]);
