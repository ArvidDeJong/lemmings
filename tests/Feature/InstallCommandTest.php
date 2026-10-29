<?php

use Darvis\Lemmings\Support\EnvironmentFile;

/**
 * `lemmings:install` generates the clear token and writes it to .env. The command works on a
 * temporary directory here, never on the .env of the Testbench app.
 */
function envDirectory(string $contents = "APP_NAME=Laravel\n"): string
{
    $directory = sys_get_temp_dir().'/lemmings-install-'.bin2hex(random_bytes(4));
    mkdir($directory);
    file_put_contents($directory.'/.env', $contents);

    app()->useEnvironmentPath($directory);

    return $directory;
}

afterEach(function () {
    foreach (glob(sys_get_temp_dir().'/lemmings-install-*') ?: [] as $directory) {
        @unlink($directory.'/.env');
        @rmdir($directory);
    }
});

it('writes a fresh token of 48 hex characters to .env and shows it once', function () {
    $directory = envDirectory();

    $this->artisan('lemmings:install')
        ->expectsOutputToContain('LEMMINGS_CLEAR_TOKEN written to .env')
        ->assertSuccessful();

    $token = (new EnvironmentFile($directory.'/.env'))->get('LEMMINGS_CLEAR_TOKEN');

    expect($token)->toMatch('/^[0-9a-f]{48}$/')
        ->and(file_get_contents($directory.'/.env'))->toStartWith("APP_NAME=Laravel\n");
});

it('keeps a token that is already there unless --force is given', function () {
    $directory = envDirectory("LEMMINGS_CLEAR_TOKEN=keep-me\nAPP_NAME=Laravel\n");

    $this->artisan('lemmings:install')
        ->expectsOutputToContain('already set')
        ->assertSuccessful();

    expect((new EnvironmentFile($directory.'/.env'))->get('LEMMINGS_CLEAR_TOKEN'))->toBe('keep-me');

    $this->artisan('lemmings:install --force')->assertSuccessful();

    $contents = (string) file_get_contents($directory.'/.env');

    // Replaced in place: the key keeps its line, the rest of the file is untouched.
    expect((new EnvironmentFile($directory.'/.env'))->get('LEMMINGS_CLEAR_TOKEN'))->toMatch('/^[0-9a-f]{48}$/')
        ->and($contents)->toMatch('/^LEMMINGS_CLEAR_TOKEN=[0-9a-f]{48}\nAPP_NAME=Laravel\n$/')
        ->and($contents)->not->toContain('keep-me');
});

it('treats an empty token as not set', function () {
    $directory = envDirectory("LEMMINGS_CLEAR_TOKEN=\n");

    $this->artisan('lemmings:install')->assertSuccessful();

    expect((new EnvironmentFile($directory.'/.env'))->get('LEMMINGS_CLEAR_TOKEN'))->toMatch('/^[0-9a-f]{48}$/');
});

it('also writes the url when asked', function () {
    $directory = envDirectory();

    $this->artisan('lemmings:install --url=https://your-own-site.example')->assertSuccessful();

    expect((new EnvironmentFile($directory.'/.env'))->get('LEMMINGS_URL'))->toBe('https://your-own-site.example');
});

it('fails without an .env file and writes nothing', function () {
    $directory = envDirectory();
    unlink($directory.'/.env');

    $this->artisan('lemmings:install')
        ->expectsOutputToContain('There is no .env file')
        ->assertFailed();

    expect(file_exists($directory.'/.env'))->toBeFalse();
});

it('quotes a value .env would otherwise misread', function () {
    $directory = envDirectory();

    (new EnvironmentFile($directory.'/.env'))->set(['LEMMINGS_URL' => 'https://example.test/?a=1 #b']);

    expect(file_get_contents($directory.'/.env'))->toContain('LEMMINGS_URL="https://example.test/?a=1 #b"')
        ->and((new EnvironmentFile($directory.'/.env'))->get('LEMMINGS_URL'))->toBe('https://example.test/?a=1 #b');
});
