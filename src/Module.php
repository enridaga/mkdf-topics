<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace MKDF\Topics;

use MKDF\Datasets\Service\DatasetsFeatureManagerInterface;
use MKDF\Topics\Feature\TopicsFeature;
use Zend\Mvc\MvcEvent;
use MKDF\Topics\Repository\MKDFTopicsRepositoryInterface;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function onBootstrap(MvcEvent $event)
    {
        // Initialisation
        $repository = $event->getApplication()->getServiceManager()->get(MKDFTopicsRepositoryInterface::class);
        $repository->init();
        $featureManager = $event->getApplication()->getServiceManager()->get(DatasetsFeatureManagerInterface::class);
        $featureManager->registerFeature($event->getApplication()->getServiceManager()->get(TopicsFeature::class));
    }

}