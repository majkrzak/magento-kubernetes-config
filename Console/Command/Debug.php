<?php

namespace Majkrzak\KubernetesConfig\Console\Command;

use Majkrzak\KubernetesConfig\Service\KubernetesApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Debug extends Command
{
    public function __construct(
        private readonly KubernetesApi $kubernetesApi,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName("kubernetes:config:debug");
        $this->setDescription("Dumps current KubernetesConfig status.");
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(\var_export($this->kubernetesApi, true));
        $output->writeln(\var_export(\iterator_to_array($this->kubernetesApi->parseAnnotations()), true));

        return 0;
    }

}
