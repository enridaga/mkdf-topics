<?php

namespace MKDF\Topics\Controller\Plugin\Factory;

use MKDF\Topics\Controller\Plugin\DatahubTopicsRepositoryPlugin;
use MKDF\Topics\Repository\MKDFTopicsRepository;
use Interop\Container\ContainerInterface;

class DatahubTopicsRepositoryPluginFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $repository = $container->get(MKDFTopicsRepository::class);
        return new DatahubTopicsRepositoryPlugin($repository);
    }
}