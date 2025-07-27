<?php

namespace Majkrzak\KubernetesConfig\Service;

use KubernetesClient\Client;
use KubernetesClient\Config;
use Majkrzak\KubernetesConfig\Data\ConfigEntry;

class KubernetesApi
{
    private readonly ?Client $client;
    private readonly ?string $podNamespace;
    private readonly ?string $podName;

    private const ANNOTATION_PREFIX = "magento.config/";
    private const NAMESPACE_FILE = "/var/run/secrets/kubernetes.io/serviceaccount/namespace";

    public function __construct()
    {
        try {
            $this->client = new Client(
                Config::LoadFromDefault()
            );
        } catch (\Error $e) {
            $this->client = null;
        }

        $this->podNamespace = \getenv("POD_NAMESPACE") ?:
            (\file_exists(self::NAMESPACE_FILE) ? \file_get_contents(self::NAMESPACE_FILE) : null);

        $this->podName = \getenv("POD_NAME") ?:
            \gethostname() ?: null;
    }

    /**
     * Tests if Kubernetes API is properly initialized.
     */
    public function isActive(): bool
    {
        return $this->client != null && $this->podNamespace != null && $this->podName != null;
    }

    /**
     * Fetch `magento.config/*` annotations of current Pod and converts them to ConfigEntries.
     */
    public function parseAnnotations(): \Generator
    {
        if (!$this->isActive()) {
            return;
        }

        foreach (((($this->client->request("/api/v1/namespaces/{$this->podNamespace}/pods/{$this->podName}") ?: []) ["metadata"] ?? []) ["annotations"] ?? []) as $key => $val) {
            if (\str_starts_with($key, self::ANNOTATION_PREFIX)) {
                yield ConfigEntry::from($key, $val);
            }
        }
    }
}
