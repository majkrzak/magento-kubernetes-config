<?php

namespace Majkrzak\KubernetesConfig\Service;

class KubernetesApi
{
    private readonly ?\KubernetesClient\Client $client;
    private readonly ?string $podNamespace;
    private readonly ?string $podName;

    private const ANNOTATION_PREFIX = "magento.config/";

    public function __construct()
    {
        try {
            $this->client = new \KubernetesClient\Client(
                \KubernetesClient\Config::LoadFromDefault()
            );
        } catch (\Error $e) {
        }

        $this->podNamespace = \getenv("POD_NAMESPACE") ?:
            \file_get_contents("/var/run/secrets/kubernetes.io/serviceaccount/namespace") ?: null;

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
        foreach (((($this->client->request("/api/v1/namespaces/${this->podNamespace}/pods/${this->podName}") ?: []) ["metadata"] ?? []) ["annotations"] ?? []) as $key => $val) {
            if (\str_starts_with($key, self::ANNOTATION_PREFIX)) {
                yield new \Majkrzak\KubernetesConfig\Data\ConfigEntry(
                    \explode(".", \substr($key, \strlen(self::ANNOTATION_PREFIX))),
                    $val
                );
            }
        }
    }
}
