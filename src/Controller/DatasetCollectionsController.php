<?php

namespace MKDF\Topics\Controller;

use MKDF\Core\Repository\MKDFCoreRepositoryInterface;
use MKDF\Topics\Repository\MKDFTopicsRepositoryInterface;
use Zend\Mvc\Controller\AbstractActionController;

class DatasetCollectionsController extends AbstractActionController
{
    private $_repository;

    public function __construct(MKDFTopicsRepositoryInterface $repository, MKDFCoreRepositoryInterface $core_repository, array $config)
    {
        $this->_config = $config;
        $this->_repository = $repository;
        $this->_core_repository = $core_repository;
    }

    public function detailsAction() {

    }
}