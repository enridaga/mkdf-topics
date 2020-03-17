<?php

namespace MKDF\Topics\Controller\Factory;

use Interop\Container\ContainerInterface;
use MKDF\Datasets\Repository\MKDFDatasetRepositoryInterface;
use MKDF\Datasets\Service\DatasetPermissionManagerInterface;
use MKDF\Topics\Controller\DatasetCollectionsController;
use MKDF\Topics\Repository\MKDFTopicsRepositoryInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class DatasetCollectionsControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get("Config");
        $repository = $container->get(MKDFTopicsRepositoryInterface::class);
        $dataset_repository = $container->get(MKDFDatasetRepositoryInterface::class);
        $permissionManager = $container->get(DatasetPermissionManagerInterface::class);
        return new DatasetCollectionsController($repository, $dataset_repository, $config, $permissionManager);
    }
}