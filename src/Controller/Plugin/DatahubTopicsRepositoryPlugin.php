<?php

namespace MKDF\Topics\Controller\Plugin;

use MKDF\Topics\Repository\MKDFTopicsRepositoryInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class DatahubTopicsRepositoryPlugin extends AbstractPlugin
{
    private $_repository;

    public function __construct(MKDFTopicsRepositoryInterface $repository)
    {
        //$this->entityManager = $entityManager;
        $this->_repository = $repository;
    }

    public function __invoke()
    {
        return $this->_repository;
    }
}