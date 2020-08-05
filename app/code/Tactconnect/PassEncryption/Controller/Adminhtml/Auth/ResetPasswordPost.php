<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tactconnect\PassEncryption\Controller\Adminhtml\Auth;

use Magento\User\Controller\Adminhtml\Auth;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Backend\Helper\Data;
use Magento\User\Model\UserFactory;

class ResetPasswordPost extends Auth
{
    /**
     * @var Data
     */
    private $backendDataHelper;

    /**
     * @param Context $context
     * @param UserFactory $userFactory
     * @param Data $backendDataHelper
     */
    public function __construct(
        Context $context,
        UserFactory $userFactory,
        Data $backendDataHelper = null,
        \Magento\Backend\Model\Session $backendSession
    ) {
        parent::__construct($context, $userFactory);
        $this->session = $backendSession;
        $this->backendDataHelper = $backendDataHelper ?: ObjectManager::getInstance()->get(Data::class);
    }
    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     * @return void
     */
    public function execute()
    {
        $passwordResetToken = (string)$this->getRequest()->getQuery('token');
        $userId = (int)$this->getRequest()->getQuery('id');
        $password = (string)$this->getRequest()->getPost('password');
        $passwordConfirmation = (string)$this->getRequest()->getPost('confirmation');
        $salt = $this->session->getBackendResetPasswordEncryptionKey();
        $key = pack("H*", $salt.$salt);
        $iv = pack("H*", $salt);
        $password = base64_decode($password);
        $password = openssl_decrypt($password , 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        $passwordConfirmation = base64_decode($passwordConfirmation);
        $passwordConfirmation = openssl_decrypt($passwordConfirmation , 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        $this->session->unsBackendResetPasswordEncryptionKey();

        try {
            $this->_validateResetPasswordLinkToken($userId, $passwordResetToken);
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('Your password reset link has expired.'));
            $this->getResponse()->setRedirect(
                $this->backendDataHelper->getHomePageUrl()
            );
            return;
        }

        /** @var $user \Magento\User\Model\User */
        $user = $this->_userFactory->create()->load($userId);
        $user->setPassword($password);
        $user->setPasswordConfirmation($passwordConfirmation);
        // Empty current reset password token i.e. invalidate it
        $user->setRpToken(null);
        $user->setRpTokenCreatedAt(null);
        try {
            $errors = $user->validate();
            if ($errors !== true && !empty($errors)) {
                foreach ($errors as $error) {
                    $this->messageManager->addError($error);
                    $this->_redirect(
                        'adminhtml/auth/resetpassword',
                        ['_nosecret' => true, '_query' => ['id' => $userId, 'token' => $passwordResetToken]]
                    );
                }
            } else {
                $user->save();
                $this->messageManager->addSuccess(__('You updated your password.'));
                $this->getResponse()->setRedirect(
                    $this->backendDataHelper->getHomePageUrl()
                );
            }
        } catch (\Magento\Framework\Validator\Exception $exception) {
            $this->messageManager->addMessages($exception->getMessages());
            $this->_redirect(
                'adminhtml/auth/resetpassword',
                ['_nosecret' => true, '_query' => ['id' => $userId, 'token' => $passwordResetToken]]
            );
        }
    }
}
