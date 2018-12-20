<?php

class Magehit_Notification_Adminhtml_NotificationController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('notifications/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('notification/notification')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('notification_data', $model);
            
			$this->loadLayout();
			$this->_setActiveMenu('notification/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('notification/adminhtml_notification_edit'))
				->_addLeft($this->getLayout()->createBlock('notification/adminhtml_notification_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('notification')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
 
	  		if (isset($data['category_ids'])) {
                $data['category_ids'] = implode(
                    ',', array_unique(explode(' ', trim(str_replace(',', ' ', $data['category_ids']))))
                ).',';
            }	
            
            if (isset($data["store_ids"])) {

                $data["store_ids"] = implode(",", $data["store_ids"]);

            }                
            
            if (isset($data["customer_group_ids"])) {

                $data["customer_group_ids"] = implode(",", $data["customer_group_ids"]);

            }
            if (isset($data["show_location"])) {

                    $data["show_location"] = implode(",", $data["show_location"]).',';

            }
            $model = Mage::getModel('notification/notification');		
			$model->setData($data)
				  ->setId($this->getRequest()->getParam('id'));
			
			try {	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('notification')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('notification')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('notification/notification');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $notificationIds = $this->getRequest()->getParam('notification');
        if(!is_array($notificationIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($notificationIds as $notificationId) {
                    $notification = Mage::getModel('notification/notification')->load($notificationId);
                    $notification->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($notificationIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $notificationIds = $this->getRequest()->getParam('notification');
        if(!is_array($notificationIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($notificationIds as $notificationId) {
                    $notification = Mage::getSingleton('notification/notification')
                        ->load($notificationId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($notificationIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'notification.csv';
        $content    = $this->getLayout()->createBlock('notification/adminhtml_notification_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'notification.xml';
        $content    = $this->getLayout()->createBlock('notification/adminhtml_notification_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
    
    public function categoriesAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('notification/adminhtml_notification_edit_tab_includecategories')->toHtml()
        );
    }

    public function categoriesJsonAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('notification/adminhtml_notification_edit_tab_includecategories')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }
}