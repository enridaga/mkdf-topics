<?php

namespace MKDF\Topics\Feature;

use MKDF\Datasets\Repository\MKDFDatasetRepositoryInterface;
use MKDF\Datasets\Service\DatasetsFeatureInterface;

class TagsFeature implements DatasetsFeatureInterface
{
    private $active = false;

    public function __construct()
    {

    }

    public function getController() {
        return \MKDF\Topics\Controller\DatasetTagsController::class;
    }
    public function getViewAction(){
        return 'details';
    }
    public function getEditAction(){
        return 'edit';
    }
    public function getViewHref($id){
        return '/dataset/tags/details/'.$id;
    }
    public function getEditHref($id){
        return '/dataset/tags/edit/'.$id;
    }
    public function hasFeature($id){
        return true;
    }
    public function getLabel(){
        return '<i class="fas fa-tags"></i> Tags';
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