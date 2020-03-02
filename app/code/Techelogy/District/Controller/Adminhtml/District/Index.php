<?php
namespace Techelogy\District\Controller\Adminhtml\District;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;


class Index extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
		
		$this->resultPage = $this->resultPageFactory->create();  
		$this->resultPage->setActiveMenu('Techelogy_District::district');
		$this->resultPage ->getConfig()->getTitle()->set((__('District')));
		return $this->resultPage;
    }
    
    protected function _isAllowed(){
		return $this->_authorization->isAllowed('Techelogy_District::techelogy_district_index');
	}
}
