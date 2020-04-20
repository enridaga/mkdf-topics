<?php


namespace MKDF\Topics\Controller;

use MKDF\Core\Repository\MKDFCoreRepositoryInterface;
use MKDF\Datasets\Repository\MKDFDatasetRepositoryInterface;
use MKDF\Datasets\Service\DatasetPermissionManager;
use MKDF\Topics\Repository\MKDFTopicsRepositoryInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class DatasetTagsController extends AbstractActionController
{
    private $_repository;
    private $_config;
    private $_dataset_repository;
    private $_permissionManager;

    public function __construct(MKDFTopicsRepositoryInterface $repository, MKDFDatasetRepositoryInterface $datasetRepository, array $config, DatasetPermissionManager $permissionManager)
    {
        $this->_config = $config;
        $this->_repository = $repository;
        $this->_dataset_repository = $datasetRepository;
        $this->_permissionManager = $permissionManager;
    }

    public function detailsAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        $dataset = $this->_dataset_repository->findDataset($id);
        $user_id = $this->currentUser()->getId();
        $can_view = $this->_permissionManager->canView($dataset,$user_id);
        $can_edit = $this->_permissionManager->canEdit($dataset,$user_id);
        $tags = $this->_repository->datasetTags($id);
        $actions = [
            'label' => 'Actions',
            'class' => '',
            'buttons' => []
        ];
        if ($can_edit) {
            //$actions['buttons'][] = ['type'=>'warning','label'=>'Edit', 'icon'=>'edit', 'target'=> 'dataset-collections', 'params'=> ['id' => $dataset->id, 'action' => 'edit']];
        }
        if ($can_view) {
            return new ViewModel([
                'dataset' => $dataset,
                'tags' => $tags,
                'features' => $this->datasetsFeatureManager()->getFeatures($id),
                'actions' => $actions
            ]);
        }
        else {
            $this->flashMessenger()->addErrorMessage('Unauthorised to view dataset.');
            return $this->redirect()->toRoute('dataset', ['action'=>'index']);
        }
    }
}