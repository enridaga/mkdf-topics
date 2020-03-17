<?php

namespace MKDF\Topics\Controller\Factory;

use Interop\Container\ContainerInterface;
use MKDF\Core\Repository\MKDFCoreRepositoryInterface;
use MKDF\Topics\Controller\DatasetCollectionsController;
use MKDF\Topics\Repository\MKDFTopicsRepositoryInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class DatasetCollectionsControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get("Config");
        $repository = $container->get(MKDFTopicsRepositoryInterface::class);
        $core_repository = $container->get(MKDFCoreRepositoryInterface::class);
        return new DatasetCollectionsController($repository, $core_repository, $config);
    }
}