<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpVendorUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpVendorUpload\Model;

use Magento\Framework\MessageQueue\ConsumerConfiguration;
use Magento\Customer\Api\AccountManagementInterface;
use Webkul\MpVendorUpload\Api\UploadProfileRepositoryInterface;
use Magento\Framework\App\RequestFactory;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Framework\Event\ManagerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;

/**
 * Class Consumer used to process OperationInterface messages.
 */
class MassConsumer extends ConsumerConfiguration
{
    const CONSUMER_NAME = "vendorupload.topic";

    const QUEUE_NAME = "vendorupload.topic";
    /**
     * @var \Magento\Framework\MessageQueue\CallbackInvoker
     */
    private $invoker;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\MessageQueue\ConsumerConfigurationInterface
     */
    private $configuration;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var OperationProcessor
     */
    private $operationProcessor;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * Initialize dependencies.
     *
     * @param AccountManagementInterface $customerAccountManagement
     * @param UploadProfileRepositoryInterface $uploadProfileFactory
     * @param RequestFactory $requestFactory
     * @param CustomerExtractor $customerExtractor
     * @param ManagerInterface $eventManager
     * @param \Webkul\MpVendorUpload\Model\ProfileAttributesFactory $profileFactory
     * @param \Webkul\MpVendorUpload\Helper\Data $mpVendorHelper
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param CustomerFactory $customerFactory
     * @param \Webkul\MpVendorUpload\Logger\Logger $vendorlogger
     */
    public function __construct(
        AccountManagementInterface $customerAccountManagement,
        UploadProfileRepositoryInterface $uploadProfileFactory,
        RequestFactory $requestFactory,
        CustomerExtractor $customerExtractor,
        ManagerInterface $eventManager,
        \Webkul\MpVendorUpload\Model\ProfileAttributesFactory $profileFactory,
        \Webkul\MpVendorUpload\Helper\Data $mpVendorHelper,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CustomerFactory $customerFactory,
        \Webkul\MpVendorUpload\Logger\Logger $vendorlogger,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        DataObjectHelper $dataObjectHelper,
        CustomerInterfaceFactory $customerApiFactory
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->uploadProfileFactory = $uploadProfileFactory;
        $this->requestFactory = $requestFactory;
        $this->customerExtractor = $customerExtractor;
        $this->eventManager = $eventManager;
        $this->profileFactory = $profileFactory;
        $this->mpVendorHelper = $mpVendorHelper;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->customerFactory = $customerFactory;
        $this->vendorlogger = $vendorlogger;
        $this->customerMapper = $customerMapper;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerApiFactory = $customerApiFactory;
    }

    /**
     * consumer process start
     * @param string $messagesBody
     * @return string
     */
    public function process($messagesBody = null)
    {
        $messagesBody = $this->mpVendorHelper->jsonDecodeData($messagesBody);
        $request = $this->requestFactory->create();
        foreach ($messagesBody as $id) {
            $cusData = $this->uploadProfileFactory->getById($id);
            $customerData = [
                'firstname' => $cusData->getFirstname(),
                'lastname' => $cusData->getLastname(),
                'email' => $cusData->getEmail(),
                'group_id' =>$cusData->getGroupId(),
                'store_id' =>$cusData->getStoreId()
            ];

            $request->setPostValue('is_seller_add', true);
            $request->setPostValue('profileurl', $cusData['profileurl']);

            if ($this->mpVendorHelper->vendorAttributeModule()) {

                $profileFactoryData = $this->profileFactory->create()->getCollection()
                    ->addFieldToFilter('profile_id', ['eq'=>$id]);
                if ($profileFactoryData->getSize() > 0) {
                    foreach ($profileFactoryData as $profileAttribute) {
                        if ($profileAttribute->getValue() == 'multiselect') {
                            $customerData[$profileAttribute->getCode()] = $this->mpVendorHelper->jsonDecodeData(
                                $profileAttribute->getValue()
                            );
                        } else {
                            $customerData[$profileAttribute->getCode()] = $profileAttribute->getValue();
                        }
                    }
                }
            }
                $msg = $this->createAccount($customerData, $cusData['profileurl'], $id);

            if ($msg) {
                $cusData->setStatusMessage('Success');
            } else {
                $cusData->setStatusMessage('Failed');
            }
            $cusData->setId($id);
            $cusData->save();
        }
        
        return $msg;
    }

    /**
     * create account for uploaded profile
     * @param  array
     * @return string
     */
    protected function createAccount($customerData, $shopUrl, $profileId = 0)
    {
        $status = false;
         
        $request = $this->requestFactory->create();
        
        $request->setParams($customerData);
        try {
            $customer = $this->customerExtractor->extract('customer_account_create', $request);
            $customer = $this->customerAccountManagement->createAccount($customer);

            $savedCustomerData = $this->customerRepositoryInterface->getById($customer->getId());
            $customerFactory = $this->customerApiFactory->create();

            $customerData = array_merge(
                $this->customerMapper->toFlatArray($savedCustomerData),
                $customerData
            );

            $customerData['id'] = $customer->getId();
            
            $this->dataObjectHelper->populateWithArray(
                $customerFactory,
                $customerData,
                \Magento\Customer\Api\Data\CustomerInterface::class
            );
            $this->customerRepositoryInterface->save($customerFactory);
            $this->vendorlogger->info("Customer created".$this->mpVendorHelper->jsonEncodeData($customerData));
            $status = true;
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->vendorlogger->info("Customer created Error".$msg);
        }
        $request->setPostValue('is_seller_add', true);
        $request->setPostValue('profileurl', $shopUrl);
        $request->setPostValue('profileId', $profileId);
        
        $this->eventManager->dispatch(
            'adminhtml_vendor_save_after',
            ['customer' => $customerFactory, 'request' =>$request]
        );
        return $status;
    }
}
