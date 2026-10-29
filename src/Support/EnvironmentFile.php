<?php

declare(strict_types=1);

namespace Darvis\Lemmings\Support;

use RuntimeException;

/**
 * Reads and writes variables in the host application's .env file.
 *
 * An existing key is replaced in place, so its position and the comments around it survive; a
 * missing key is appended at the end.
 */
final class EnvironmentFile
{
    public function __construct(private readonly string $path) {}

    public function exists(): bool
    {
        return is_file($this->path);
    }

    /**
     * The raw value of a variable, or null when it is absent or empty.
     */
    public function get(string $key): ?string
    {
        if (! $this->exists()) {
            return null;
        }

        $pattern = '/^'.preg_quote($key, '/').'=(.*)$/m';

        if (! preg_match($pattern, (string) file_get_contents($this->path), $matches)) {
            return null;
        }

        $value = trim(trim($matches[1]), '"\'');

        return $value === '' ? null : $value;
    }

    /**
     * Set the given variables, replacing any existing values.
     *
     * @param  array<string, string>  $variables
     */
    public function set(array $variables): void
    {
        if (! $this->exists()) {
            throw new RuntimeException("No environment file found at {$this->path}.");
        }

        $contents = (string) file_get_contents($this->path);

        foreach ($variables as $key => $value) {
            $line = $key.'='.$this->format($value);
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

            if (preg_match($pattern, $contents)) {
                // The replacement is literal: a backslash or a dollar in the value must not be read as a reference.
                $contents = (string) preg_replace($pattern, str_replace(['\\', '$'], ['\\\\', '\\$'], $line), $contents, 1);

                continue;
            }

            $contents = rtrim($contents, "\n")."\n".$line."\n";
        }

        file_put_contents($this->path, $contents);
    }

    /**
     * Quote a value only when .env would otherwise misread it.
     */
    private function format(string $value): string
    {
        return preg_match('/[\s#"\'$]/', $value) ? '"'.addcslashes($value, '"\\').'"' : $value;
    }
}
