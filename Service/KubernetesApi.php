<?php

namespace Majkrzak\KubernetesConfig\Service;

class KubernetesApi
{
    private readonly \KubernetesClient\Client $client;
    private readonly string $podNamespace;
    private readonly string $podName;

    public function __construct()
    {
        $this->client = new \KubernetesClient\Client(
            \KubernetesClient\Config::LoadFromDefault()
        );

        $this->podNamespace = \getenv("POD_NAMESPACE") ?:
            \file_get_contents("/var/run/secrets/kubernetes.io/serviceaccount/namespace") ?:
            "default";

        $this->podName = \getenv("POD_NAME") ?:
            \gethostname() ?:
            "";
    }

    /**
     * Fetch `magento.config/*` annotations of current pod and converts them to ConfigEntries.
     *
     * @return Generator<\Majkrzak\KubernetesConfig\Data\ConfigEntry>
     */
    public function parseAnnotations(): \Generator
    {
        foreach (((($this->client->request("/api/v1/namespaces/${this->podNamespace}/pods/${this->podName}") ?: []) ["metadata"] ?? []) ["annotations"] ?? []) as $key => $val) {
            yield new \Majkrzak\KubernetesConfig\Data\ConfigEntry([], "");
        }
    }
}
