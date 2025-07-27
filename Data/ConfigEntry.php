<?php

namespace Majkrzak\KubernetesConfig\Data;

class ConfigEntry
{
    public function __construct(
        public readonly array $path,
        public readonly string $value,
    ) {
    }

    /**
     * Create ConfigEntry out of key/val string pair.
     *
     * Expects key to be in form of dot separated snake case labels,
     * like: `foo_bar.bar.foo`. To simplify handling of annotations,
     * it also ignore prefix up to the last /.
     */
    public static function from(string $key, string $val): ConfigEntry
    {
        return new ConfigEntry(
            \explode(".", \substr($key, (strrpos($key, "/") ?: -1) + 1)),
            $val,
        );
    }

    /**
     * Apply this entry to given array
     */
    public function applyTo(array &$ptr): void
    {
        foreach ($this->path as $key) {
            if (!\array_key_exists($key, $ptr)) {
                $ptr[$key] = [];
            }
            $ptr = &$ptr[$key];
        }

        $ptr = $this->value;
    }
}
