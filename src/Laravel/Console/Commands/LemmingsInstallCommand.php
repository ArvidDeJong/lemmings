<?php

declare(strict_types=1);

namespace Darvis\Lemmings\Laravel\Console\Commands;

use Darvis\Lemmings\Support\EnvironmentFile;
use Darvis\Lemmings\Support\LemmingsConfig;
use Illuminate\Console\Command;

/**
 * Generates the clear token and writes it to .env, so the maintenance route and the packages
 * page work right after `composer require`. The token is shown once; it is never logged.
 */
class LemmingsInstallCommand extends Command
{
    protected $signature = 'lemmings:install
        {--force : Replace a token that is already in .env}
        {--url= : Also write LEMMINGS_URL, the address the umbrella links to}';

    protected $description = 'Generate the LEMMINGS_CLEAR_TOKEN and write it to .env';

    public function handle(): int
    {
        $env = new EnvironmentFile($this->laravel->environmentFilePath());

        if (! $env->exists()) {
            $this->components->error('There is no .env file. Run "cp .env.example .env" first, then run this command again.');

            return self::FAILURE;
        }

        $url = $this->option('url');

        if (is_string($url) && $url !== '') {
            $env->set(['LEMMINGS_URL' => $url]);
            $this->components->info("LEMMINGS_URL set to {$url}.");
        }

        if ($env->get('LEMMINGS_CLEAR_TOKEN') !== null && ! $this->option('force')) {
            $this->components->warn('LEMMINGS_CLEAR_TOKEN is already set in .env. Run the command with --force to replace it.');

            return self::SUCCESS;
        }

        $token = bin2hex(random_bytes(24));

        $env->set(['LEMMINGS_CLEAR_TOKEN' => $token]);

        $this->components->info('LEMMINGS_CLEAR_TOKEN written to .env. Copy it now; it is not shown again:');
        $this->newLine();
        $this->line('  '.$token);
        $this->newLine();
        $this->components->bulletList([
            'Clear the caches: curl -H "X-Lemmings-Token: <token>" '.url('/clearDgP'),
            'See the packages:  '.url(LemmingsConfig::packagesRoute()).'?token=<token>',
            'When the config is cached, run "php artisan config:cache" again.',
            '.env is not in git: run this command on every server as well.',
        ]);

        return self::SUCCESS;
    }
}
