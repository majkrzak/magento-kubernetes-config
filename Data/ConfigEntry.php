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
