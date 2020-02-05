<?php
namespace MKDF\Topics\Controller\Factory;

use MKDF\Topics\Controller\CollectionController;
use MKDF\Topics\Repository\MKDFTopicsRepositoryInterface;
use MKDF\Core\Repository\MKDFCoreRepositoryInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Zend\Session\SessionManager;

class CollectionControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get("Config");
        $repository = $container->get(MKDFTopicsRepositoryInterface::class);
        $core_repository = $container->get(MKDFCoreRepositoryInterface::class);
        $sessionManager = $container->get(SessionManager::class);
        return new CollectionController($repository, $core_repository, $config);
    }
}