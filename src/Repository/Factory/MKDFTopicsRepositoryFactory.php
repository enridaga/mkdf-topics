<?php


namespace MKDF\Topics\Repository\Factory;

use MKDF\Topics\Repository\MKDFTopicsRepository;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class MKDFTopicsRepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get("Config");
        return new MKDFTopicsRepository($config);
    }
}