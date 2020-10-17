<?php


namespace Webkul\MpVendorUpload\Controller\Adminhtml\Upload;

class Index extends \Webkul\MpVendorUpload\Controller\Adminhtml\Upload
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_Marketplace::marketplace');
        $resultPage->getConfig()->getTitle()->prepend(__('Vendor Mass Upload'));
        $resultPage->addLeft(
            $resultPage->getLayout()->createBlock(
                \Webkul\MpVendorUpload\Block\Adminhtml\Upload\Edit\Tabs::class
            )
        );
        return $resultPage;
    }
}
