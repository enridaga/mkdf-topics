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
        $tagList = $this->_repository->getAllTags();
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
                'tagList' => $tagList,
                'features' => $this->datasetsFeatureManager()->getFeatures($id),
                'actions' => $actions
            ]);
        }
        else {
            $this->flashMessenger()->addErrorMessage('Unauthorised to view dataset.');
            return $this->redirect()->toRoute('dataset', ['action'=>'index']);
        }
    }

    public function addAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        //$ownerName = $this->params()->fromQuery('inputOwner', '');
        $user_id = $this->currentUser()->getId();
        $dataset = $this->_dataset_repository->findDataset($id);
        $can_edit = $this->_permissionManager->canEdit($dataset,$user_id);

        if(!$this->getRequest()->isPost()) {
            $this->flashMessenger()->addErrorMessage('Incorrect parameters supplied.');
            return $this->redirect()->toRoute('dataset-tags', ['action'=>'details', 'id' => $id]);
        }
        $tagName = $this->params()->fromPost('inputTag', '');
        //Check for missing params
        if ($tagName == '') {
            $this->flashMessenger()->addErrorMessage('Incorrect parameters supplied.');
            return $this->redirect()->toRoute('dataset-tags', ['action'=>'details', 'id' => $id]);
        }

        if ($can_edit) {
            $outcome = $this->_repository->addDatasetTag($id, strtolower($tagName));
            if ($outcome == 1){
                $this->flashMessenger()->addSuccessMessage('The tag was added to the dataset.');
            }
            else {
                $this->flashMessenger()->addSuccessMessage('The tag is already assigned to the dataset.');
            }
            return $this->redirect()->toRoute('dataset-tags', ['action'=>'details', 'id' => $id]);
        }
        else {
            $this->flashMessenger()->addErrorMessage('Unauthorised to edit dataset tags.');
            return $this->redirect()->toRoute('dataset-tags', ['action'=>'details', 'id' => $id]);
        }
    }

    public function deleteAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        $datasetTagId = $this->params()->fromQuery('tag_id', '');
        $user_id = $this->currentUser()->getId();
        $dataset = $this->_dataset_repository->findDataset($id);

        //Check for missing params
        if ($datasetTagId == '') {
            $this->flashMessenger()->addErrorMessage('Incorrect parameters supplied.');
            return $this->redirect()->toRoute('dataset-tags', ['action'=>'details', 'id' => $id]);
        }

        $can_edit = $this->_permissionManager->canEdit($dataset,$user_id);
        $messages = [];
        if($can_edit){
            $this->_repository->deleteDatasetTag($datasetTagId);

            $this->flashMessenger()->addSuccessMessage('Tag removed.');
            return $this->redirect()->toRoute('dataset-tags', ['action'=>'details', 'id' => $id]);
        }else{
            $this->flashMessenger()->addErrorMessage('Unauthorised to edit dataset tags.');
            return $this->redirect()->toRoute('dataset-tags', ['action'=>'details', 'id' => $id]);
        }
    }
}