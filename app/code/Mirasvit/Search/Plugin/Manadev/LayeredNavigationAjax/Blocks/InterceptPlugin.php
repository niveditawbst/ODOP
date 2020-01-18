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



namespace Mirasvit\Search\Plugin\Manadev\LayeredNavigationAjax\Blocks;

use Magento\Framework\View\Element\Template;

class InterceptPlugin extends Template
{
    public function beforeRefreshStayingInSameCategory($subject, $block)
    {
        if ($block == 'search.result'){
            $block = 'searchindex.result';
        }
        return $block;
    }
}
