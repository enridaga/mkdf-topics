<?php
namespace MKDF\Topics\Controller;

use MKDF\Core\Entity\Dataset;
use MKDF\Core\Repository\MKDFCoreRepositoryInterface;
use MKDF\Topics\Repository\MKDFTopicsRepositoryInterface;
use MKDF\Topics\Form\CollectionForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter;
use Zend\View\Model\ViewModel;
use Zend\Session\SessionManager;
use Zend\Session\Container;

class CollectionController extends AbstractActionController
{
    private $_config;
    private $_repository;

    public function __construct(MKDFTopicsRepositoryInterface $repository, MKDFCoreRepositoryInterface $core_repository, array $config)
    {
        $this->_config = $config;
        $this->_repository = $repository;
        $this->_core_repository = $core_repository;
    }
    
    public function indexAction()
    {
        $user = $this->currentUser();
        $actions = [];
        //anonymous/logged-out user will return an ID of -1
        $userId = $user->getId();
        if ($userId > 0) {
            $actions = [
                'label' => 'Actions',
                'class' => '',
                'buttons' => [[ 'class' => '', 'type' => 'primary', 'icon' => 'create', 'label' => 'Create a new collection', 'target' => 'collection', 'params' => ['action' => 'add']]]
            ];
        }

        $collections = $this->_repository->findAllCollections();

        $paginator = new Paginator(new Adapter\ArrayAdapter($collections));
        $page = $this->params()->fromQuery('page', 1);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(10);

        return new ViewModel([
            'message' => 'Collections ',
            'collections' => $paginator,
            'currentUserId'   => $userId,
            'url_params' => $this->params()->fromQuery(),
            'actions' => $actions
        ]);
    }
    
    public function detailsAction() {
        $userId = $this->currentUser()->getId();
        $id = (int) $this->params()->fromRoute('id', 0);
        $collection = $this->_repository->findCollection($id);
        $message = "Collection //" . $id;
        return new ViewModel([
            'message' => $message,
            'collection' => $collection,
            'datasets' => $this->_repository->datasetsInCollection($id, $userId),
            'userCanEdit' => ($collection->user_id == $userId)
        ]);
    }
    
    public function addAction(){
        $form = new CollectionForm();
        // Check if user has submitted the form
        $messages = [];
        if($this->getRequest()->isPost()) {
          $data = $this->params()->fromPost();
          $form->setData($data);
          if($form->isValid()){
              // Get User Id
              $user_id = $this->currentUser()->getId();
              // Write data
              $id = $this->_repository->insertCollection(['title' => $data['title'], 'description'=>$data['description'],'user_id'=>$user_id]);
              // Redirect to "view" page
              $this->flashMessenger()->addSuccessMessage('A new collection was created.');
              return $this->redirect()->toRoute('collection', ['action'=>'index']);
          }else{
                $messages[] = [ 'type'=> 'warning', 'message'=>'Please check the content of the form.'];
          }
        } 
        // Pass form variable to view
        return new ViewModel(['form' => $form, 'messages' => $messages ]);
    }
    public function editAction(){
        $id = (int) $this->params()->fromRoute('id', 0);
        $collection = $this->_repository->findCollection($id);
        $user_id = $this->currentUser()->getId();
        $can_edit = ($collection->user_id == $user_id);
        $messages = [];
        if($can_edit){
            $form = new CollectionForm();
            if($this->getRequest()->isPost()) {
                $data = $this->params()->fromPost();
                $form->setData($data);
                if($form->isValid()){
                      // Get User Id
                      $user_id = $this->currentUser()->getId();
                      // Write data
                      $id = $this->_repository->updateCollection($data['id'], $data['title'], $data['description']);
                      // Redirect to "view" page
                      $this->flashMessenger()->addSuccessMessage('The collection was updated succesfully.');
                      return $this->redirect()->toRoute('collection', ['action'=>'index']);
                }else{
                    $messages[] = [ 'type'=> 'warning', 'message'=>'Please check the content of the form.'];
                }
            } else{
                $form->setData($collection->getProperties());
            }
            // Pass form variable to view
            return new ViewModel(['form' => $form, 'messages' => $messages ]);
        }else{
            // FIXME Better handling security
            throw new \Exception('Unauthorized');
        }
    }
    
    public function deleteAction(){
        // 
        $id = (int) $this->params()->fromRoute('id', 0);
        $token = $this->params()->fromQuery('token', '');
        $collection = $this->_repository->findCollection($id);
        if($collection == null){
            throw new \Exception('Not found');
        }
        $user_id = $this->currentUser()->getId();
        $can_edit = ($collection->user_id == $user_id);
        $container = new Container('Collections_Management');
        $valid_token = ($container->delete_token == $token);
        if($can_edit && $valid_token){
            $outcome = $this->_repository->deleteCollection($id);
            unset($container->delete_token);
            $this->flashMessenger()->addSuccessMessage('The collection was deleted succesfully.');
            return $this->redirect()->toRoute('collection', ['action'=>'index']);
        }else{
            // FIXME Better handling security
            throw new \Exception('Unauthorized. Delete token was ' . (($valid_token)?'valid':'invalid') . '.');
        }
    }
    
    public function deleteConfirmAction(){
        // 
        $id = (int) $this->params()->fromRoute('id', 0);
        $collection = $this->_repository->findCollection($id);
        $user_id = $this->currentUser()->getId();
        $can_edit = ($collection->user_id == $user_id);
        if($can_edit){
            $token = uniqid(true);
            $container = new Container('Collections_Management');
            $container->delete_token = $token;
            $messages[] = [ 'type'=> 'warning', 'message' =>
                'Are you sure you want to delete this collection?'];
            return new ViewModel(['collection' => $collection, 'token' => $token, 'messages' => $messages]);
        }else{
            // FIXME Better handling security
            throw new \Exception('Unauthorized');
        }
    }
    
    public function removeDatasetAction(){
        $id = (int) $this->params()->fromRoute('id', 0);
        $collection = $this->_repository->findCollection($id);
        $user_id = $this->currentUser()->getId();
        $can_edit = ($collection->user_id == $user_id);
        if($can_edit){
            $dataset_id = (int) $this->params()->fromQuery('dataset_id', 0);
            $this->_repository->removeFromCollection($id, $dataset_id);
            $this->flashMessenger()->addSuccessMessage('The collection was updated succesfully.');
            return $this->redirect()->toRoute('collection', ['action'=>'details', 'id'=>$id]);
        }else{
            // FIXME Better handling security
            throw new \Exception('Unauthorized');
        }
    }
    public function selectDatasetsAction(){
        $id = (int) $this->params()->fromRoute('id', 0);
        $collection = $this->_repository->findCollection($id);
        $user_id = $this->currentUser()->getId();
        $can_edit = ($collection->user_id == $user_id);
        if($can_edit){
            if($this->getRequest()->isPost()) {
                // Get posted data, add datasets, and redirect to details
                $datasets =  $this->params()->fromPost('dataset', []);
                $this->_repository->addToCollection($id, $datasets);
                $this->flashMessenger()->addSuccessMessage('The collection was updated succesfully.');
                return $this->redirect()->toRoute('collection', ['action'=>'details', 'id'=>$id]);
            }else{
                $datasetCollection = $this->_repository->datasetsNotInCollection($id, $user_id);
                return new ViewModel([
                    'collection' => $collection,
                    'userCanEdit' => $can_edit,
                    'datasets' => $datasetCollection
                ]);
            }
        }else{
            // FIXME Better handling security
            throw new \Exception('Unauthorized');
        }
    }
    
}
