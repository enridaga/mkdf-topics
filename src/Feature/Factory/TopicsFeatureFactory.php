<?php

namespace MKDF\Topics\Feature\Factory;

use Interop\Container\ContainerInterface;
use MKDF\Topics\Feature\TopicsFeature;
use Zend\ServiceManager\Factory\FactoryInterface;

class TopicsFeatureFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get("Config");
        return new TopicsFeature();
    }
}