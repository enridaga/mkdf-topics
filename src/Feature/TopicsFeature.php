<?php

namespace MKDF\Topics\Feature;

use MKDF\Datasets\Repository\MKDFDatasetRepositoryInterface;
use MKDF\Datasets\Service\DatasetsFeatureInterface;

class TopicsFeature implements DatasetsFeatureInterface
{
    private $active = false;

    public function __construct()
    {

    }

    public function getController() {
        return \MKDF\Topics\Controller\DatasetCollectionsController::class;
    }
    public function getViewAction(){
        return 'details';
    }
    public function getEditAction(){
        return 'edit';
    }
    public function getViewHref($id){
        return '/dataset/collection/details/'.$id;
    }
    public function getEditHref($id){
        return '/dataset/stream/edit/'.$id;
    }
    public function hasFeature($id){
        return true;
    }
    public function getLabel(){
        return '<i class="fas fa-tags"></i> Tags and Collections';
    }
    public function isActive(){
        return $this->active;
    }
    public function setActive($bool){
        $this->active = !!$bool;
    }

    public function initialiseDataset($id) {

    }
}