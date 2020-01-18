<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search
 * @version   1.0.137
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Plugin\Manadev\ProductCollection\Contracts\FilterResource;

use Magento\Store\Model\StoreManagerInterface;

class ApplyPlugin
{
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ){
        $this->storeManager = $storeManager;
    }

    public function aroundApply($subject, $proceed, $select, \Manadev\ProductCollection\Contracts\Filter $filter, $callback)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Manadev\ProductCollection\Factory $factory */
        $factory = $om->create('Manadev\ProductCollection\Factory');
        /** @var \Magento\Search\Model\AdapterFactory */
        $adapter = $om->create('Magento\Search\Model\AdapterFactory')->create();
        /** @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorage $storage */
        $storage = $om->create('Magento\Framework\Search\Adapter\Mysql\TemporaryStorage');
        $requestBuilder = $factory->createRequestBuilder();
        $requestBuilder->bindDimension('scope', $this->storeManager->getStore()->getId());
        $requestBuilder->bind('search_term', $filter->getText());
        $requestBuilder->setRequestName('quick_search_container');
        $request = $requestBuilder->create();
        $response = $adapter->query($request);
        $table = $storage->storeApiDocuments($response);
        $select->joinInner(['search_result' => $table->getName()],
            'e.entity_id = search_result.' . \Magento\Framework\Search\Adapter\Mysql\TemporaryStorage::FIELD_ENTITY_ID, []);

        return ;
    }
}
